<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\BusinessPlanQuestion;
use App\Models\BusinessPlanAnswer;
use App\Models\BusinessPlanQuestionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\OpenAIService;
use GuzzleHttp\Client;
use App\Models\BusinessPlanSubmission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Parsedown;
use App\Services\ModuleSettingService;

class BusinessPlanController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function index(Request $request,ModuleSettingService $moduleSettingService)
    {
        if (! $moduleSettingService->isModuleEnabled('businessplan')) {
             return redirect()->route('student.my-workspace');
        }

        if (!in_array(Session::get('student_id'), [72, 834])) {
            abort(403, 'You are not authorized to access this feature.');
        }
        
        Session::forget('business_plan_access_granted');
        Session::forget('business_plan_form_token');
        $formToken = Str::uuid()->toString();
        Session::put('form_token', $formToken);
        $maxStep = BusinessPlanQuestion::max('step'); // Only run once
        Session::put('max_step', $maxStep);
        return view('student.businessplan.questionnaire', [
            'totalSteps' => $maxStep
        ]);
    }

    public function getQuestion(Request $request)
    {
        $stepTitles = [
            1 => "Welcome & Introduction",
            2 => "What Problem Do You See?",
            3 => "Your Solution",
            4 => "Your Unique Selling Proposition",
            5 => " Set Your Goals",
            6 => "Describe Your Product/Service",
            7 => "Know Your Customer",
            8 => "Pricing Wizard",
            9 => "Marketing Maestro",
            10 => "Financial Plan"
        ];
        $step = $request->input('step', 1);
        $studentId = Session::get('student_id');
        $formToken = Session::get('form_token');
        $maxStep = Session::get('max_step', 10);

        $questions = BusinessPlanQuestion::with([
            'options:id,business_plan_question_id,option_text,option_value',
            'answers' => function ($q) use ($studentId, $formToken) {
                $q->where('student_id', $studentId)
                    ->where('form_token', $formToken); // optional
            }
        ])
            ->where('step', $step)
            ->orderBy('display_order')
            ->select('id', 'step', 'question_type', 'question_value', 'display_order', 'prompt_text', 'placeholder_text', 'is_required')
            ->get();

        if ($questions->isEmpty()) {
            return response()->json(['html' => '<p class="text-success">🎉 All questions completed!</p>', 'is_last' => true]);
        }

        $currencies = ($step == 8) ? DB::table('currencies')->get() : null;


        // Determine if it's the last step
        // $maxStep = BusinessPlanQuestion::max('step');
        $isLast = ($step == $maxStep);

        $html = view('student.businessplan.partials.question', [
            'questions' => $questions,
            'is_last' => $isLast,
            'currencies' => $currencies,
        ])->render();

        return response()->json([
            'html' => $html,
            'is_last' => $isLast,
            'step_title' => 'STEP ' . $step . ': ' . ($stepTitles[$step] ?? '')
        ]);
    }

    public function saveAnswer(Request $request)
    {
        $studentId = Session::get('student_id');
        $questionIds = $request->input('question_id', []);
        $currencyInputs = $request->input("currency_id", []);
        $formToken = Session::get('form_token');

        foreach ($questionIds as $questionId) {
            $question = BusinessPlanQuestion::with('options')->find($questionId);
            if (!$question) continue;

            // $customText = $request->input("custom_text.$questionId");
            $responseText = $request->input("response_text.$questionId");
            $selectedOptionId = $request->input("selected_option_id.$questionId");
            // $selectedOptions = $request->input("selected_options.$questionId", []);
            $currencyId = $currencyInputs[$questionId] ?? null;
            $businessPlanId = $question->business_plan_id;


            if ($question->question_type === 'radio') {
                $option = $question->options->firstWhere('id', $selectedOptionId);

                BusinessPlanAnswer::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'form_token' => $formToken,
                        'question_id' => $questionId,
                    ],
                    [
                        'selected_option_id' => $selectedOptionId,
                        'selected_option_value' => $option->option_value ?? null,
                        'currency_id' => $currencyId,
                        'business_plan_id' => $businessPlanId,
                    ]
                );
            } elseif (in_array($question->question_type, ['text', 'textarea'])) {
                BusinessPlanAnswer::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'form_token' => $formToken,
                        'question_id' => $questionId,
                    ],
                    [
                        'response_text' => $responseText,
                        'currency_id' => ($question->step == 8 ? $currencyId : null),
                        'business_plan_id' => $businessPlanId,
                    ]
                );
            }
        }

        // Always create/update submission record to track progress
        // Get the business_plan_id from the first question (they should all be the same)
        // $firstQuestion = BusinessPlanQuestion::find($questionIds[0]);
        // $businessPlanId = $firstQuestion ? $firstQuestion->business_plan_id : null;

        // if ($businessPlanId) {
        //     if ($request->input('final_step')) {
        //         Session::put('business_plan_form_token', $formToken); // Save for PDF use

        //         return response()->json([
        //             'success' => true,
        //             'redirect' => route('student.businessplan.result')
        //         ]);
        //     } else {
        //         // Create/update submission record as in-progress (is_submit = 0)
        //         BusinessPlanSubmission::updateOrCreate(
        //             [
        //                 'student_id' => $studentId,
        //                 'business_plan_id' => $businessPlanId,
        //             ],
        //             [
        //                 'is_submit' => 0,
        //                 'submission_date' => null, // Track when they last worked on it
        //             ]
        //         );
        //     }
       // }
        if ($request->input('final_step')) {
            $formToken = Session::get('form_token');
            Session::put('business_plan_form_token', $formToken);
            return response()->json([
                'success' => true,
                'redirect' => route('student.businessplan.result')
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function showResult()
    {
        $formToken = Session::get('form_token');
        $completedToken = Session::get('business_plan_form_token');

        // Only allow if the last saved token matches the current session token
        if (!$formToken || !$completedToken || $formToken !== $completedToken) {
            return redirect()->route('student.businessplan.index');
        }
        return view('student.businessplan.result');
    }

    public function generatePDFPrompt($formToken)
    {
        $answers = BusinessPlanAnswer::with('question')
            ->where('form_token', $formToken)
            ->whereHas('question', function ($q) {
                $q->where('step', '>', 1);
            })
            ->orderBy('question_id')
            ->get();

        // Define the key mapping for specific questions
        $questionKeys = [
            'problem_focus' => "What's one problem you want to focus on?",
            'solution_offering' => "What is the solution you are offering?",
            'unique_idea' => "What is unique or special about your idea?",
            'items_per_year' => "How many items do you plan to sell in 1 year?",
            'offering_description' => "Describe what you're offering and explain how it works.",
            'target_audience' => "Who needs or wants your solution the most?",
            'cost_per_unit' => "What is the cost per unit?",
            'time_and_cost' => "Calculate the time it takes to make 1 unit or offer your service once and how much you think your time should cost.",
            'profit_per_unit' => "How much profit do you want to make per unit?",
            'marketing_strategy' => "Think about your audience: Where do they spend time, and what kind of message would excite them about your idea. Give as many details as possible?",
            'other_expenses' => "Apart from making your product, what other things do you spend money on? Let's list those fixed or extra costs",
            'total_fixed_cost' => "Based on the above, provide the total fixed cost for 1 year.",
            'growth_rate' => "By what percentage will the business grow every year?"
        ];

        // Extract answers by question text matching with fuzzy matching
        $extractedAnswers = [];
        $usedQuestionIds = []; //  prevent duplicate matches

        foreach ($questionKeys as $key => $questionText) {
            $answer = $answers->first(function ($a) use ($questionText, $usedQuestionIds) {
                if (in_array($a->question_id, $usedQuestionIds)) {
                    return false;
                }

                $dbQuestion = trim(strtolower($a->question->question_value));
                $searchQuestion = trim(strtolower($questionText));

                if ($dbQuestion === $searchQuestion) {
                    return true;
                }

                $dbWords = array_diff(explode(' ', $dbQuestion), ['what', 'is', 'the', 'a', 'an', 'you', 'your', 'and', 'or', 'of', 'to', 'in', 'on', 'at', 'for', 'with', 'by']);
                $searchWords = array_diff(explode(' ', $searchQuestion), ['what', 'is', 'the', 'a', 'an', 'you', 'your', 'and', 'or', 'of', 'to', 'in', 'on', 'at', 'for', 'with', 'by']);

                $matchCount = count(array_intersect($dbWords, $searchWords));
                $minWords = min(count($dbWords), count($searchWords));

                return $minWords > 0 && ($matchCount / $minWords) > 0.6;
            });

            if ($answer) {
                $text = $answer->response_text;
                if ($answer->selected_option_value) {
                    $text .= ' ' . $answer->selected_option_value;
                }
                if ($answer->custom_text) {
                    $text .= ' ' . $answer->custom_text;
                }

                $extractedAnswers[$key] = trim($text);
                $usedQuestionIds[] = $answer->question_id;

                Log::info("Found match for key '$key':", [
                    'question' => $answer->question->question_value,
                    'answer' => $text
                ]);
            } else {
                $extractedAnswers[$key] = '';
                Log::info("No match found for key '$key' with question:", ['question' => $questionText]);
            }
        }

        // Fallback if many are empty
        $emptyCount = count(array_filter($extractedAnswers, fn($v) => empty($v)));
        if ($emptyCount > 10) {
            Log::info('Using fallback step-based approach');
            $extractedAnswers['items_per_year'] = (string) ($answers->firstWhere('question.step', 5)?->response_text ?? '');
            $extractedAnswers['cost_per_unit'] = (string) ($answers->first(fn($a) => $a->question->step == 8 && $a->question->display_order == 1)?->response_text ?? '');
            $extractedAnswers['time_and_cost'] = (string) ($answers->first(fn($a) => $a->question->step == 8 && $a->question->display_order == 2)?->response_text ?? '');
            $extractedAnswers['profit_per_unit'] = (string) ($answers->first(fn($a) => $a->question->step == 8 && $a->question->display_order == 3)?->response_text ?? '');
            $extractedAnswers['total_fixed_cost'] = (string) ($answers->first(fn($a) => $a->question->step == 10 && $a->question->display_order == 2)?->response_text ?? '');
            $extractedAnswers['growth_rate'] = (string) ($answers->first(fn($a) => $a->question->step == 10 && $a->question->display_order == 3)?->response_text ?? '');
            Log::info('Fallback extracted answers:', ['fallback_answers' => $extractedAnswers]);
        }

        // Cast values
        $costPerUnit = (float) ($extractedAnswers['cost_per_unit'] ?? 0);
        $itemsPerYear = (float) ($extractedAnswers['items_per_year'] ?? 0);
        $profitPerUnit = (float) ($extractedAnswers['profit_per_unit'] ?? 0);
        $totalFixedCost = (float) ($extractedAnswers['total_fixed_cost'] ?? 0);
        $growthRate = (float) ($extractedAnswers['growth_rate'] ?? 0);
        $timeCost = (float) filter_var($extractedAnswers['time_and_cost'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        $sellingPricePerUnit = $costPerUnit + $timeCost + $profitPerUnit;

        $forecast = [];
        // for ($year = 1; $year <= 5; $year++) {
        //     $revenue = $itemsPerYear * $sellingPricePerUnit;
        //     $grossProfit = $itemsPerYear * $profitPerUnit;
        //     $netProfit = $grossProfit - $totalFixedCost;

        //     $forecast[] = [
        //         'year' => "Year $year",
        //         'revenue' => round($revenue),
        //         'gross_profit' => round($grossProfit),
        //         'net_profit' => round($netProfit),
        //     ];

        //     $itemsPerYear *= (1 + $growthRate / 100);
        // }

        // Prepare GPT prompt
        $compiledAnswers = '';
        foreach ($questionKeys as $key => $questionText) {
            if (!empty($extractedAnswers[$key])) {
                $compiledAnswers .= "- {$questionText}: {$extractedAnswers[$key]}\n";
            }
        }

        
        $currencyId = $answers
    ->first(fn($a) => $a->question->step == 8 && $a->question->display_order == 1)
    ?->currency_id;

     $currencySymbol = DB::table('currencies')->find($currencyId)?->symbol ?? '₹';
        // Prompt for OpenAI (without financial table)
        $prompt = <<<EOT
You are a business planning assistant for students under 12 years old.

Please generate a detailed business plan using the following answers given by a student.

Problem: {$extractedAnswers['problem_focus']}

Solution: {$extractedAnswers['solution_offering']}

Unique Selling Proposition: {$extractedAnswers['unique_idea']}

Goal: {$extractedAnswers['items_per_year']}

Product Description: {$extractedAnswers['offering_description']}

Target Customers:{$extractedAnswers['target_audience']}

Pricing:
- Cost per unit = {$currencySymbol}{$extractedAnswers['cost_per_unit']} 
- Time cost ={$currencySymbol} {$extractedAnswers['time_and_cost']}
- Profit per unit = {$currencySymbol}{$extractedAnswers['profit_per_unit']}

Marketing:{$extractedAnswers['marketing_strategy']}

Financial Plan: 
Fixed Costs:
-{$extractedAnswers['other_expenses']}
Total Fixed Cost = {$currencySymbol}{$extractedAnswers['total_fixed_cost']}

Expected Growth Rate = {$extractedAnswers['growth_rate']}% per year

Revenue should be calculated as: ({$currencySymbol}{$extractedAnswers['cost_per_unit']} + {$currencySymbol}{$extractedAnswers['time_and_cost']} + {$currencySymbol}{$extractedAnswers['profit_per_unit']}) × {$extractedAnswers['items_per_year']}.

Now generate a full business plan including:
- Summary of idea, highlights, strengths, and risks
- Yearly financial projection table for 5 years(Revenue, Gross Profit, Net Profit)
- Daily, Weekly, Monthly, and Yearly action steps
- A tracker/checklist format to measure progress

Write in a way that's easy for a kid to understand and fun to read. Use headings like: Business Name, Highlights, Strengths, Risks, Financial Table, Action Plan, Tracker/Checklist.

EOT;
        return [
            'prompt' => $prompt,
            // 'forecast' => $forecast,
            // 'extracted_answers' => $extractedAnswers, // Optional: return extracted answers for debugging
        ];
    }

    public function generateBusinessPlanPDFAsync(Request $request)
    {
        $formToken = session('business_plan_form_token');
        $tenantId = tenant()->tenant_id;
        $studentId = session('student_id');

        try {
            // Generate prompt and financial forecast
            $result = $this->generatePDFPrompt($formToken);
            $prompt = $result['prompt'];
            // $forecast = $result['forecast'];

            // Generate HTML content using OpenAI
            $businessPlanText = $this->openAIService->generateBusinessPlan($prompt);
            $aiRawText = <<<EOT
            $businessPlanText
            EOT;
            $parsedown = new Parsedown();
            $html = $parsedown->text($aiRawText); // convert markdown to HTML


            // Generate and save PDF file
            $pdf = \PDF::loadView('student.businessplan.businessplan_pdf', [
                'html' => $html,
                // 'forecast' => $forecast,
                // 'extracted_answers' => $result['extracted_answers'],
            ]);

            $folderPath = public_path("tenants/{$tenantId}/student/businessplan");
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $fileName = 'businessplan_' . Str::random(8) . '.pdf';
            $fullPath = $folderPath . '/' . $fileName;
            $relativePath = "tenants/{$tenantId}/student/businessplan/{$fileName}";

            $pdf->save($fullPath);

            // Get business_plan_id before updateOrCreate
            $businessPlanId = BusinessPlanAnswer::where('form_token', $formToken)
                ->where('student_id', $studentId)
                ->value('business_plan_id');

            // Save submission
            if ($businessPlanId) {
                BusinessPlanSubmission::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'business_plan_id' => $businessPlanId,
                    ],
                    [
                        'file_path' => $relativePath,
                        'is_submit' => 1,
                        'submission_date' => now(),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'download_route' => route('student.businessplan.download-pdf'),
            ]);
        } catch (\Exception $e) {
            Log::error('PDF Generation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $studentId,
                'form_token' => $formToken
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while generating your business plan PDF.',
            ], 500);
        }
    }

    public function downloadBusinessPlanPDF(Request $request)
    {
        $studentId = session('student_id');

        $submission = BusinessPlanSubmission::where('student_id', $studentId)
            ->latest()
            ->first();

        if ($submission && File::exists(public_path($submission->file_path))) {
            return response()->download(public_path($submission->file_path), 'BusinessPlan.pdf');
        }

        return back()->with('error', 'Business Plan PDF not found.');
    }
}
