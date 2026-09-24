<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Services\ModuleSettingService;

class PrototypeController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function prototype(Request $request, ModuleSettingService $moduleSettingService)
    {    
        if (! $moduleSettingService->isModuleEnabled('prototype_my_idea')) {
        return redirect()->route('student.my-workspace');
    }

        if (!in_array(Session::get('student_id'), [72, 834])) {
            abort(403, 'You are not authorized to access this feature.');
        }

        return view('student.prototype.index');
    }

    /*
    public function generatePrototype(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB max
        ]);

        $imagePath = $request->file('image')->store('public/uploads');
        $imageFullPath = storage_path('app/' . $imagePath);
        $imageData = base64_encode(file_get_contents($imageFullPath));

        $response = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Describe this image and suggest a UI prototype based on it.'],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:image/jpeg;base64,' . $imageData,
                            ],
                        ],
                    ],
                ],
            ],
            'max_tokens' => 100
        ]);

        $result = $response->json();

        echo '<pre>';
        print_r($result);
        die();

        return view('student.prototype.result', [
            'prototypeDescription' => $result['choices'][0]['message']['content'] ?? 'No description generated.',
        ]);
    }
    */  
    /*
    public function generatePrototype(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
            'prompt' => 'nullable|string|max:500',
        ]);

        // Save uploaded image
        $imagePath = $request->file('image')->store('public/uploads');
        $fullImagePath = storage_path('app/' . $imagePath);
        $imageData = base64_encode(file_get_contents($fullImagePath));

        // Get student-provided prompt, or fallback
        $userPrompt = trim($request->input('prompt'));
        $defaultPrompt = "Analyze this sketch and describe a clean, modern UI prototype that could be generated from it.";
        $fullPrompt = $userPrompt ?: $defaultPrompt;

        // $fullPrompt = "Create a realistic, professionally rendered prototype based on this hand-drawn sketch by a student. The prototype should maintain the core concept and functionality shown in the sketch. Transform the drawing into a clean, modern design. Show proper engineering/design principles while keeping the original creative intent. Make it look like a real product that could actually be built. Use realistic colors and lighting.";

        //  $fullPrompt = "This is a student's project sketch. Create a detailed DALL-E 3 prompt to generate a realistic, professional prototype that maintains the core concept but looks like it could actually be built. Focus on realistic materials, proper engineering, and professional product design.";

        //  $fullPrompt ="Convert this student's hand-drawn sketch into a photorealistic prototype. Transform the drawing into a real, professionally manufactured product while keeping the original design concept. Professional product photography, realistic materials, white background, sharp lighting.";

          $fullPrompt ="Convert this student's hand-drawn sketch into a single photorealistic prototype.  Transform the drawing into a real, professionally manufactured product while keeping the original design concept. Generate exactly one object only. Professional product photo of one individual item, centered, white background, no duplicates or multiple versions.";

        
        // Step 1: Describe the image using GPT-4 Vision
        $visionResponse = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $fullPrompt],
                    ['type' => 'image_url', 'image_url' => ['url' => 'data:image/jpeg;base64,' . $imageData]],
                ]
            ]],
            'max_tokens' => 100
        ]);

        $description = $visionResponse['choices'][0]['message']['content'] ?? 'A modern UI layout based on the sketch.';
        
        // Step 2: Generate UI Prototype using DALL·E
        $imageResponse = Http::withToken(env('OPENAI_API_KEY'))->post('https://api.openai.com/v1/images/generations', [
            'model' => 'dall-e-3',
            // 'prompt' => "Create a polished flying umbrella based on this description: " . $description,
            'prompt' => $description,
            // 'prompt' => "convert this sketch into prototype based on this description: " . $description,
            'n' => 1,
            'size' => '1024x1024',
        ]);

        $aiImageUrl = $imageResponse['data'][0]['url'] ?? null;

        return view('student.prototype.result', [
            'aiImageUrl' => $aiImageUrl,
            'description' => $description,
            'studentPrompt' => $userPrompt ?: '(used default prompt)',
        ]);
    }
    */
    public function generatePrototype(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240' // 10MB
        ]);

         // Store the sketch
        $sketchPath = $request->file('image')->store('sketches', 'public');
        
        // Prepare answers (fill empty ones with default text)
        $answers = [
            'appearance' => $request->appearance ?: 'Not specified',
            'function' => $request->function ?: 'Not specified',
            'environment' => $request->environment ?: 'Not specified',
            'feeling' => $request->feeling ?: 'Not specified',
        ];

        // Generate prototype with smart analysis
        $result = $this->openAIService->generatePrototype(
            Storage::disk('public')->path($sketchPath), 
            $answers
        );

        // echo '<pre>';
        // print_r($result);
        // die();

        return view('student.prototype.result', [
            'aiImageUrl' => $result['prototype_url'],
        ]);
    }

}