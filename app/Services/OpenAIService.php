<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OpenAIService
{
    private $client;
    private $apiKey;
    protected $chatCompletionApiUrl;
    protected $imageApiUrl;
    protected $topicPromptTemplate = '';
    private $lastError;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.openai.api_key');
        $this->chatCompletionApiUrl  = config('services.openai.chat_completion_api_url');
        $this->imageApiUrl = config('services.openai.image_api_url');
    }

    public function generatePrototype($sketchPath, $answers)
    {
        // Step 1: Check if answers are adequate
        // $needsSketchAnalysis = $this->needsSketchAnalysis($answers);
        
        $result = [
            'prototype_url' => null,
            'sketch_analysis' => null,
            'analysis_used' => false
        ];
        
        // if ($needsSketchAnalysis) {
            // Step 2: Analyze sketch first to get detailed description
            $sketchAnalysis = $this->analyzeSketch($sketchPath);
            if ($sketchAnalysis) {
                $result['sketch_analysis'] = $sketchAnalysis;
                $result['analysis_used'] = true;
                
                // Step 3: Generate prototype with sketch analysis + student answers
                $result['prototype_url'] = $this->generatePrototypeWithAnalysis($sketchAnalysis, $answers);
            }
        // } else {
            // Step 4: Generate prototype with student answers only
            // $result['prototype_url'] = $this->generatePrototypeFromAnswers($answers);
        // }
        
        return $result;
    }

    private function needsSketchAnalysis($answers)
    {
        // Check if any answer is too short, vague, or empty
        foreach ($answers as $answer) {
            if (empty(trim($answer)) || strlen(trim($answer)) < 10 || 
                $this->isVagueAnswer($answer)) {
                return true;
            }
        }
        return false;
    }

    private function isVagueAnswer($answer)
    {
        $vagueWords = ['good', 'nice', 'cool', 'awesome', 'thing', 'stuff', 'idk', 'dunno', 'maybe'];
        $answer = strtolower(trim($answer));
        
        foreach ($vagueWords as $word) {
            if (strpos($answer, $word) !== false && strlen($answer) < 20) {
                return true;
            }
        }
        return false;
    }

//     public function analyzeSketch($sketchPath)
//     {
//         // Encode image to base64
//         $imageData = base64_encode(file_get_contents($sketchPath));
        
//         try {
//             $response = $this->client->post($this->chatCompletionApiUrl, [
//                 'headers' => [
//                     'Authorization' => 'Bearer ' . $this->apiKey,
//                     'Content-Type' => 'application/json',
//                 ],
//                 'json' => [
//                     'model' => 'gpt-4o', // 'gpt-4-vision-preview',
//                     'messages' => [
//                         [
//                             'role' => 'user',
//                             'content' => [
//                                 [
//                                     'type' => 'text',
//                                     'text' => 'Analyze this student\'s sketch and provide detailed information in this exact format:

// APPEARANCE: [Describe shape, size, colors, materials, components, and visual features you can see]
// FUNCTION: [What does this invention appear to do? What problem does it solve? What are its capabilities?]
// ENVIRONMENT: [Based on the design, where would this be used? What environment is it suited for?]
// FEELING: [What mood or emotion does this design convey? Modern, playful, serious, magical, etc.]

// Be specific and detailed. If something is unclear, make reasonable assumptions based on the drawing style and components visible.'
//                                 ],
//                                 [
//                                     'type' => 'image_url',
//                                     'image_url' => [
//                                         'url' => "data:image/jpeg;base64,{$imageData}"
//                                     ]
//                                 ]
//                             ]
//                         ]
//                     ],
//                     'max_tokens' => 500
//                 ]
//             ]);

//             $data = json_decode($response->getBody(), true);
//             return $data['choices'][0]['message']['content'] ?? null;

//         } catch (\Exception $e) {
//             \Log::error('Sketch Analysis Error: ' . $e->getMessage());
//             return null;
//         }
//     }

    private function generatePrototypeWithAnalysis($sketchAnalysis, $studentAnswers)
    {
        $prompt = "Convert this student's hand-drawn sketch into ONE single photorealistic prototype.

SKETCH ANALYSIS:
{$sketchAnalysis}

STUDENT INPUT (use when detailed, otherwise rely on sketch analysis):
Student's Appearance Description: {$studentAnswers['appearance']}
Student's Function Description: {$studentAnswers['function']}
Student's Environment Description: {$studentAnswers['environment']}  
Student's Feeling Description: {$studentAnswers['feeling']}

Requirements:
- Show only ONE object in the entire image
- No duplicates or multiple versions
- Prioritize sketch analysis details, enhance with student input
- Make it look like it could actually be manufactured
- Professional product photography
- Realistic materials and engineering
- Clean white background
- Centered composition

Style: High-quality product render, photorealistic, professionally manufactured prototype.";

        return $this->callDALLE($prompt);
    }

    private function generatePrototypeFromAnswers($answers)
    {
        $prompt = "Convert this student's hand-drawn sketch into ONE single photorealistic prototype.

STUDENT SPECIFICATIONS:
APPEARANCE: {$answers['appearance']}
SPECIAL FUNCTION: {$answers['function']}
ENVIRONMENT/USE: {$answers['environment']}
DESIRED FEELING: {$answers['feeling']}

Requirements:
- Show only ONE object in the entire image
- No duplicates or multiple versions
- Incorporate all described materials, colors, and features
- Make it look like it could actually perform the special function
- Design it for the specified environment
- Capture the desired emotional feeling through design
- Professional product photography
- Realistic materials and engineering
- Clean white background
- Centered composition

Style: High-quality product render, photorealistic, professionally manufactured prototype.";

        return $this->callDALLE($prompt);
    }

    public function callDALLE($prompt)
    {
        try {
            $response = $this->client->post($this->imageApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'size' => '1024x1024',
                    // 'quality' => 'hd',
                    // 'n' => 1,
                    // 'response_format' => 'url'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return $data['data'][0]['url'] ?? null;

        } catch (\Exception $e) {
            \Log::error('DALL-E API Error: ' . $e->getMessage());
            return null;
        }
    }

     private function callOpenAI(array $messages, int $maxTokens = 1000): ?string
    {
        try {
            $response = $this->client->post($this->chatCompletionApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'temperature' => 0.7,
                    'messages' => $messages,
                    'max_tokens' => $maxTokens,
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $this->lastError = null;
            return $data['choices'][0]['message']['content'] ?? null;

        } catch (\Exception $e) {
            $this->lastError = $this->extractOpenAIErrorMessage($e);
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return null;
        }
    }

    private function extractOpenAIErrorMessage(\Exception $e): string
    {
        if ($e instanceof RequestException && $e->hasResponse()) {
            $decoded = json_decode((string) $e->getResponse()->getBody(), true);
            if (isset($decoded['error']['message'])) {
                return $decoded['error']['message'];
            }
        }

        return $e->getMessage();
    }

    public function generateObservationSummary(string $firstName, array $observations): ?string
    {
        $lines = collect($observations)->map(function ($obs) {
            return '- ' . $obs['name'] . ': ' . $obs['short_note'];
        })->implode("\n");

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are writing a student observation summary for a entrepreneurship program. Based on anecdotal observations recorded by a trainer, write a 5-6 sentence professional summary in third person. Mention what was visibly observed and also note qualities that may not have been directly seen but are likely present based on the evidence. Be warm, encouraging, and suitable for parents and educators. Use the student\'s first name. Return only the summary paragraph with no headings or extra text.',
            ],
            [
                'role'    => 'user',
                'content' => "Student first name: {$firstName}\n\nObservations:\n{$lines}",
            ],
        ];

        return $this->callOpenAI($messages, 250);
    }

    public function rephraseAnecdote(string $text, string $observationName): ?string
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are an expert educator helping teachers write professional student observation notes. Rephrase the given anecdotal note in a clear, professional, and educator-appropriate tone suitable for a student progress report. Keep it concise (2-4 sentences), factual, and focused on observable behaviour. Return only the rephrased text with no extra commentary.',
            ],
            [
                'role'    => 'user',
                'content' => "Observation trait: {$observationName}\n\nTeacher's note: {$text}",
            ],
        ];

        return $this->callOpenAI($messages, 200);
    }

    public function generateBusinessPlan(string $prompt)
    {
        $messages = [[
            'role' => 'user',
            'content' => $prompt
        ]];

        return $this->callOpenAI($messages);
    }

    public function generateTopics(string $prompt, int $maxTokens = 1000): ?string
    {
        $messages = [[
            'role' => 'user',
            'content' => $prompt
        ]];

        return $this->callOpenAI($messages, $maxTokens);
    }

    public function generateTopicsForContext(string $grade, string $board, string $country, string $subject, int $count = 10, int $maxTokens = 1500): ?string
    {
        $prompt = $this->buildTopicPrompt($grade, $board, $country, $subject, $count);

        if ($prompt === '') {
            return null;
        }

        return $this->generateTopics($prompt, $maxTokens);
    }

    public function generateSubjectiveQuestions(string $prompt, int $maxTokens = 2200): ?string
    {
        $messages = [[
            'role' => 'user',
            'content' => $prompt
        ]];

        return $this->callOpenAI($messages, $maxTokens);
    }

    public function generateMcqQuestions(string $prompt, int $maxTokens = 2200): ?string
    {
        $messages = [[
            'role' => 'user',
            'content' => $prompt
        ]];

        return $this->callOpenAI($messages, $maxTokens);
    }

    public function generateAssessmentReport(string $prompt, int $maxTokens = 2000): ?string
    {
        $messages = [[
            'role' => 'user',
            'content' => $prompt
        ]];

        return $this->callOpenAI($messages, $maxTokens);
    }

    public function buildMcqAssessmentReportPrompt(string $grade, string $board, string $country, array $responses): string
    {
        $responsesJson = json_encode($responses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return <<<PROMPT
Role: You are an expert assessment analyst interpreting a student's responses to REAL IQ MCQ-based scenario questions.
Your task is to detect thinking patterns across multiple situations, not judge single answers.
Generate a multi-parameter, tendency-based profile using both quantitative levels and qualitative narrative.

Purpose:
Using a student's responses to MCQ questions, evaluate thinking tendencies across 5 parameters:
1. Problem Awareness
2. Creative Thinking
3. Empathy
4. Value Creation
5. Subject Matter Expertise

Core principles:
- Do not assess from one choice.
- No option is all right or all wrong.
- Each option is a signal of thinking tendency.
- Insight must come from aggregated tendencies across all responses.
- This is pattern recognition over time, not one-shot judgement.

Produce:
- Parameter-wise evidence distributions
- Overall level per parameter (Beginning / Emerging / Good Evidence / Strong Evidence)
- A growth-oriented narrative report describing how the student tends to think and decide

Input context:
- Grade: {$grade}
- Board: {$board}
- Country: {$country}
- MCQ responses and option data:
{$responsesJson}

Do NOT use:
- Student name
- School name
- Any personally identifiable data

Core Interpretation Model:
1. Option-Level Evidence (Internal Signal Layer)
For each MCQ:
•	Each option is pre-tagged with evidence signals across:
o	Problem Awareness
o	Creative Thinking
o	Empathy
o	Value Creation
o	Subject Matter Expertise
Important rules:
•	Do not treat any option as “correct”
•	Do not treat any option as “wrong”
•	Treat each option as a signal of a thinking tendency
•	A single option may show:
o	Strong value creation but weak subject use, or
o	Good subject use but limited empathy, etc.
2. Aggregation Across 10 MCQs (Pattern Layer)
For each parameter:
•	Collect the evidence signals from the student's choices across all 10 MCQs
•	Look for:
o	Frequency of stronger vs weaker evidence
o	Consistency vs variability
o	Direction of tendency (e.g., often practical but rarely empathetic, etc.)
Then assign one overall level per parameter:
•	Beginning - Mostly minimal or unclear evidence across situations
•	Emerging - Some relevant signs, but inconsistent or limited
•	Good Evidence - Clear and repeated signs in many situations
•	Strong Evidence - Consistent, balanced, and thoughtful signs across most situations
Critical Rule:
•	Do not average mechanically.
•	Decide levels based on patterns and stability of evidence, not points.

Output Required:
The system must generate three layers of output:
1. Layer 1: Parameter Evidence Summary (Quantitative)
For each of the 5 parameters, provide:
•	Overall Level:
o	Beginning / Emerging / Good Evidence / Strong Evidence
•	Evidence Snapshot (short, 1-2 lines):
o	What kinds of choices the student tends to make across situations
Example format:
•	Problem Awareness: Good Evidence
o	The student often chooses options that recognise the main issue, though sometimes focuses on surface details.
(Repeat for all 5 parameters.)
2. Layer 2: Pattern Insights (Qualitative, Analytical)
Provide short, clear insights such as:
•	Where the student is most consistent
•	Where the student is still uneven or developing
•	How the student:
o	Balances people vs problems
o	Balances ideas vs practicality
o	Uses subject knowledge in decisions
This should read like an analyst's interpretation, not a score report.
3. Layer 3: Narrative Student Report (Growth-Oriented)
Write a 2-4 paragraph narrative that:
•	Explains:
o	How the student tends to notice problems
o	How they approach solutions
o	How they think about others
o	How they create value
o	How they use subject knowledge
•	Uses:
o	Simple, positive, growth-oriented language
o	No labels, no ranking, no predictions
•	Emphasises:
o	Tendencies over time, not single choices
o	Strengths and next growth edges
•	Avoids:
o	Test-like language
o	"Right/wrong" framing
o	Comparison with others

Quality Rules for Interpretation:
You MUST:
• Treat this as pattern recognition, not scoring
• Value consistency across situations more than one-off strong signals
• Allow mixed profiles (e.g., strong value creation, emerging subject use)
• Use growth-oriented language
• Do NOT:
o	Over-interpret a single choice
o	Convert this into marks or grades
o	Label the student's ability or future
o	Compare the student to others

Guardrails (Critical):
• If all 10 MCQs are treated as equal “points,” rewrite the logic
• If the report implies “You are X because you chose Y once,” rewrite
• If the report says “Across situations, you often tend to…,” keep
• If the narrative sounds like an exam result, rewrite
• If the narrative sounds like a thinking and decision-making profile, keep

Output Format (Strict):
Section A: Parameter Profile
•	Problem Awareness: Overall Level
o	Evidence Summary: (1-2 lines)
•	Creative Thinking: Overall Level
o	Evidence Summary: (1-2 lines)
•	Empathy: Overall Level
o	Evidence Summary: (1-2 lines)
•	Value Creation: Overall Level
o	Evidence Summary: (1-2 lines)
•	Subject Matter Expertise: Overall Level
o	Evidence Summary: (1-2 lines)
Section B: Pattern Insights
(3-6 bullet points or short paragraphs describing key tendencies and contrasts in the student's choices across the 10 MCQs.)
Section C: Narrative Report (Student-Facing)
(2-4 short paragraphs, plain language, growth-oriented, describing how the student tends to think and decide across situations.)

Output plain text only. No markdown.
PROMPT;
    }

    public function buildSubjectiveAssessmentReportPrompt(string $grade, string $board, string $country, string $parameters, string $rubrics, array $responses): string
    {
        $responsesJson = json_encode($responses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return <<<PROMPT
Role: 
You are an expert assessment analyst evaluating student responses to REAL IQ subjective, scenario-based questions. Your task is to identify evidence of thinking across the provided parameters, recognise that one response can show different levels on different parameters, and produce a balanced qualitative + quantitative profile. You must evaluate thinking quality, not language quality.

Purpose:
Using a student's responses to the provided subjective questions:
- Assess each response across these parameters:
{$parameters}
- Recognise that:
1. A single answer can show different levels across different parameters
2. The student is not judged by one question, but by patterns across all questions
- Produce:
1. Per-question, per-parameter evidence levels
2. Aggregated parameter ratings across all questions
3. A narrative report that explains how the student tends to think and decide

Input context:
- Grade: {$grade}
- Board: {$board}
- Country: {$country}
- The topics used for each question 
- Student subjective responses:
{$responsesJson}

Do NOT use:
- Student name
- School name
- Any personally identifiable data

Core Evaluation Model:
- Each response must be evaluated independently across these parameters:
{$parameters}
- Each parameter is rated on this evidence scale:
{$rubrics}
- Critical Rule:
1. Do not force the same level across all parameters for a response.
2. A student may show:
• Strong empathy but weak subject use
• Good problem awareness but emerging creativity
• Good subject use but weak value creation
- This mixed profile is expected and desired.

Output Required:
The system must produce three layers of output:
1. Per-Question Evidence Map (Diagnostic Layer)
- For each question, output:
•	A table or structured block showing each parameter from the list above, formatted as:
{$parameters}
(Level + short evidence note per parameter)
Evidence notes must:
•	Quote or paraphrase what the student actually did
•	Focus on thinking and decisions, not language quality
2. Aggregated Parameter Ratings (Profile Layer)
- Across all questions, aggregate evidence to produce one overall level per parameter:
{$parameters}
- Use these rubric levels for the Overall Level:
{$rubrics}
- Format each parameter as:
1. Overall Level
2. Summary (1-2 lines on pattern across answers)
Rules for aggregation:
1. Do not average mechanically
2. Look for patterns and consistency across responses
3. Weigh:
•	Frequency of stronger evidence
•	Stability vs inconsistency
•	Growth within the set (if visible)
- Also provide:
•	A 1-2 line explanation per parameter summarising the pattern seen
3. Narrative Student Report (Interpretation Layer)
Generate a plain-language narrative that:
- Is written directly to the student in second person using "You" and "Your"
- Does not refer to the student as "the student", "they", or "their"
- Describes:
•	How the student tends to notice problems
•	How they generate ideas
•	How they think about others
•	How they create value
•	How they use subject knowledge
- Uses:
•	Growth-oriented, non-judgmental language
•	No labels, no predictions, no ranking
- Focuses on:
•	Thinking tendencies
•	Decision-making style
•	Strengths and next areas to grow

Quality Rules for Evaluation:
- When assessing responses, you MUST:
•	Evaluate thinking quality, not grammar or vocabulary
•	Look for evidence, not intentions
•	Allow partial and mixed evidence
•	Avoid:
o	Over-penalising simple language
o	Over-rewarding long or polished answers
•	Be growth-oriented, not deficit-focused
•	Do NOT:
o	Compare the student to others
o	Predict future performance
o	Turn this into a score-only judgement
Guardrails (Critical):
- If an answer is vague or generic, mark Beginning/Emerging where appropriate and note why
- If an answer is long but does not show reasoning, do not upgrade levels
- If an answer shows empathy but no subject use, reflect that difference in ratings
- If an answer shows a small but clear idea applied correctly, that can be Good Evidence
- If an answer balances trade-offs, people, and subject ideas clearly, that can be Strong Evidence

Output Format (Strict JSON only):
Return a single JSON object with this exact shape:
{
  "Section A": {
    "Question 1": {
      "Parameter Name - Description": "Level - Evidence note",
      "Parameter Name - Description": "Level - Evidence note"
    }
  },
  "Section B": {
    "Parameter Name - Description": {
      "Overall Level": "Use one of the rubric levels listed above",
      "Summary": "1-2 line summary"
    }
  },
  "Section C": "Student-facing narrative report (2-4 short paragraphs) written directly to the student using You/Your"
}
Rules:
- Use the provided parameters list exactly for keys in Section A and Section B.
- Use the provided rubric levels for the Overall Level value.
- Section C must address the student directly, for example: "You demonstrate..." and "Your use of subject knowledge...".
- Section C must not use third-person phrasing such as "The student demonstrates...", "They tend to...", or "Their response...".
- Output valid JSON only. No markdown, no preface, no extra keys.
PROMPT;
    }

    public function buildTopicPrompt(string $grade, string $board, string $country, string $subject, int $count = 10): string
    {
        $template = $this->topicPromptTemplate !== '' ? $this->topicPromptTemplate : $this->getTopicPromptTemplate();

        return str_replace(
            ['{grade}', '{board}', '{country}', '{subject}', '{count}'],
            [$grade, $board, $country, $subject, $count],
            $template
        );
    }

    public function setTopicPromptTemplate(string $template): void
    {
        $this->topicPromptTemplate = $template;
    }

    public function buildSubjectiveQuestionPrompt(string $grade, string $board, string $country, string $subject, string $topic, string $parameters, int $count = 5): string
    {
        return str_replace(
            ['{grade}', '{board}', '{country}', '{subject}', '{topic}', '{$parameters}', '{count}'],
            [$grade, $board, $country, $subject, $topic, $parameters, $count],
            $this->getSubjectiveQuestionPromptTemplate()
        );
    }

    public function buildMcqQuestionPrompt(string $grade, string $board, string $country, string $subject, string $topic, int $count = 5): string
    {
        return str_replace(
            ['{grade}', '{board}', '{country}', '{subject}', '{topic}', '{count}'],
            [$grade, $board, $country, $subject, $topic, $count],
            $this->getMcqQuestionPromptTemplate()
        );
    }

    private function getTopicPromptTemplate(): string
    {
        return <<<'PROMPT'
Purpose
Generate curriculum-aligned, age-appropriate, child-friendly topics for the selected subject.
based on:
Selected Grade: {grade}
Board: {board}
Country: {country}
Subject: {subject}
Topic Count (per subject): {count}

Grade Alignment Rule: If a child selects Grade N, generate topics primarily from Grade N-1 curriculum (the previous grade), since the child has just entered Grade N and may not yet be familiar with Grade N content.
Example:
If Grade 6 is selected -> Use Grade 5 topics
If Grade 7 is selected -> Use Grade 6 topics
If Grade 8 is selected -> Use Grade 7 topics
This ensures:
Fairness
No advantage to students who have already been exposed to the new grade syllabus
Focus on application of learned knowledge, not fresh content

Inputs (Minimal & Privacy-Safe)
Selected Grade: {grade}
Board: {board}
Country: {country}
Subject: {subject}

Do NOT use:
Student name
School name
City
Personal background
Any identifiable data

Output Required
For the given inputs, generate:
{count} topics for the selected subject:
Subject: {subject}
From Grade (Selected Grade - 1) syllabus of the chosen board & country

Topic Quality Rules
Each topic must:
- Come from the previous grade's syllabus for that board & country
- Be commonly taught and recognisable to teachers of that board
- Be age-appropriate and syllabus-relevant
- Use child-friendly wording appropriate to the selected grade
- Be neutral and inclusive
- Be usable later for real-world application and assessment design
Must NOT be:
- A question
- A scenario
- A project
- Mixed subjects (keep them subject-wise here)

Output Format (Strict)
Output JSON only in this exact shape:
{
  "topics": [
    "Topic 1",
    "Topic 2",
    "Topic 3"
  ]
}

Built-in Guardrails
Before finalising:
- If topics are from the selected grade instead of previous grade -> Reject
- Are these exam questions? -> Reject
- Are these project titles? -> Reject
- Are these scenarios? -> Reject
- If topics match the previous grade syllabus -> Accept
- Are these clean, syllabus-style topics? -> Accept
- If a teacher would recognise them as standard syllabus topics -> Accept

Hard constraints:
- Return exactly {count} items.
- Output valid JSON only, no markdown, no preface.
PROMPT;
    }

    private function getSubjectiveQuestionPromptTemplate(): string
    {
        return <<<'PROMPT'
Role: You are an expert assessment designer creating REAL IQ subjective questions that measure real-world application, creative thinking, empathy, value creation, and subject knowledge and not writing skill or memorisation.

Purpose:
1. generate {count} subjective, scenario-based questions.
2. Each question must:
- Be rooted in the selected subject and topic context
- Place the student in simple, real-life situations
- Ask the student to notice a problem, think of simple creative solutions, consider how others feel, explain how their idea can solve the above problem and create value, and demonstrate how you applied subject knowledge
- Reveal how the student thinks and decides, not how well they write
- Use age-appropriate, child-friendly language for the selected grade
3. For this run, use the selected context:
- Grade: {grade}
- Board: {board}
- Country: {country}
- Subject: {subject}
- Topic: {topic}
4. Do NOT use:
- Student name
- School name
- City
- Any personal or identifiable data

Output Required:
Generate {count} subjective questions, each with:
1. A simple real-life scenario (2-4 short paragraphs, easy language)
2. A clear problem or challenge that sounds genuinely like a real-life problem
3. A thinking task that asks the student to respond in their own words, focusing on:
- What is the problem they notice?
- What idea(s) do they suggest?
- How does this help others?
- How does this create value?
- What subject knowledge do they use?
Each question must be open-ended but structured, so answers cannot be generic or copy-paste.

Assessment Parameters (Must Be Built Into Every Question):
Each subjective question must be designed to surface evidence of
- {parameters}

Subjective Question Quality rules (Strict):
1. Each question MUST:
- Be cross-disciplinary in context (even if anchored in one subject)
- Be age-appropriate and inclusive
- Use simple language
- Use child-friendly wording appropriate to the selected grade
- Have a real-life problem that can have multiple reasonable solutions
- Focus on thinking and reasoning, not writing quality
- Encourage value creation, not just “fixing” something
- Make it hard to answer with → A generic paragraph, A template-style response, An AI-like vague answer
2. Each question MUST NOT:
- Be a direct exam-style question
- Have a single correct answer
- Test grammar, vocabulary, or presentation
- Require special background, resources, or experiences
- Be a long project or essay brief

Built-in Guardrails:
Before finalising each question, check:
- Can a student answer this without really thinking about the situation? → Rewrite
- Can the same answer fit many different questions? → Add context or constraints
- Does this reward fancy language? → Simplify and refocus on decisions
- Does this reveal how the student notices problems, thinks, and decides? → Keep

Output JSON only in this exact shape:
{
  "questions": [
    {
      "scenario": "2-4 short paragraphs, simple real-life context",
      "challenge": "1 short paragraph about what is not working"
    }
  ]
}

Additional strict output rules:
- Output JSON only, no markdown, no preface, no explanations.
- Use double quotes for all keys/strings.
- Do not include trailing commas.
- "scenario" and "challenge" must be non-empty for each item.
- Return exactly {count} items.
PROMPT;
    }

    private function getMcqQuestionPromptTemplate(): string
    {
        return <<<'PROMPT'
Role:
You are an expert assessment designer creating REAL IQ MCQs that surface thinking patterns over time, not right/wrong answers.
Each question contributes partial evidence across multiple parameters.
Meaningful insight emerges only after aggregating responses across all MCQs.

Purpose:
Generate {count} MCQs for this run that can later combine with other MCQs into a larger assessment set.
Use this context:
- Grade: {grade}
- Board: {board}
- Country: {country}
- Subject: {subject}
- Topic: {topic}

Context-lock rule:
- Build all scenarios from the provided context only (Grade/Board/Country/Subject/Topic).
- Do not reuse fixed sample stories, fixed characters, or repeated templates from previous outputs.
- Adapt difficulty, vocabulary, and examples to the provided grade and topic.
- Keep situations culturally neutral while still plausible for the specified country/board context.

Each question must:
- Are set in non-trivial, real-life, age-appropriate situations
- Present trade-offs and stakeholder tensions
- Ask the student to choose the option that best matches their thinking
- Do NOT have “right” or “wrong” answers in isolation
- Use age-appropriate, child-friendly language for the selected grade

Every MCQ should contribute evidence about:
1. Problem Awareness
2. Creative Thinking
3. Empathy
4. Value Creation
5. Subject Matter Expertise

Measurement model (critical):
For each MCQ:
- Include 4 options (A/B/C/D).
- Every option must be reasonable.
- No option is completely wrong.
- No option is perfect on all dimensions.
- Options should reflect different evidence levels of thinking:
  - Beginning evidence
  - Emerging evidence
  - Good evidence
  - Strong evidence
- Options differ in:
  - How well they notice the real problem
  - How creative or flexible the idea is
  - How much they consider others
  - How much value they create
  - How well they use subject knowledge
- Option order must be mixed across questions (do not always make A weakest or D strongest).

MCQ quality rules (strict):
Each MCQ MUST:
- Require judgement, not memorization or recall.
- Use simple, inclusive, context-neutral language.
- Do not depend on student identity, school, location, or privilege.
- Do not include any person names (for example: Riya, Alex, John, Meera). Use neutral references like "a student", "a group", "the class", "a team", "a community member".
- Do not include silly, throwaway, or obviously bad options.
- Do not create a single clearly correct answer in isolation.
- Avoid textbook or exam-style phrasing.

Non-trivial problem rule:
Reject scenarios that can be solved by obvious one-step actions.
Accept scenarios that require compromise, priorities, and balancing impact across different people.

Option Design Guardrails
Before finalising options:
- If one option is “clearly stupid” → Rewrite
- If one option is “perfect on all 5 parameters” → Rewrite
- If three options are weak and one is strong → Rewrite
- If each option represents a distinct quality of thinking pattern → Keep
- If real insight only appears when many questions are answered together → Good

Question stem variation rule:
- Keep the decision intent consistent, but vary wording across items.
- Do not repeat the exact same question sentence in every MCQ.
- Use alternatives such as:
  - Which choice balances this situation best?
  - What is the most effective next step here?
  - Which option is most likely to create sustainable value?
  - Which response handles the trade-offs most responsibly?

Output JSON only in this exact shape:
{
  "questions": [
    {
      "based_topics": ["{topic}"],
      "scenario": "2–4 short paragraphs, simple, realistic, inclusive",
      "Question": "A decision-focused question line with wording that is unique for this item",
      "options": {
        "A": "Reasonable option representing one evidence pattern",
        "B": "Reasonable option representing a different evidence pattern",
        "C": "Reasonable option representing a different evidence pattern",
        "D": "Reasonable option representing a different evidence pattern"
      },
      "correct_option": "A",
      "explanation": "Internal calibration note only. Briefly describe evidence pattern levels across A/B/C/D; do not call any option absolutely right or wrong."
    }
  ]
}

Hard constraints:
- Return exactly {count} items.
- `scenario`, `Question`, and all options must be non-empty.
- `correct_option` must be one of A/B/C/D and is used only as internal calibration for storage compatibility.
- Do not keep `correct_option` fixed to one letter; vary it across questions according to option quality in that item.
- Do not use identical wording for the decision question line across all items.
- Do not include personal names anywhere in scenario, question, or options.
- Do not say that one option is the only correct answer.
- Output valid JSON only, no markdown, no preface.
PROMPT;
    }

    public function buildSchoolReportPromptTemplate(array $payload): string
    {
        $data = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Role:
You are an education assessment analyst and school improvement advisor. Your task is to synthesize student-level subjective assessment results into a clear, growth-oriented, actionable School Report. This report is not a ranking. It is a diagnostic and improvement tool. The report will be moderated and edited by the school before being shared with stakeholders.

Purpose:
Create a school-level report that:
- Summarizes:
  - How many students participated
  - From which grades
- Analyzes:
  - How students performed on average across the provided parameters
- Shows:
  - Percentage distribution of students across all levels for each parameter
- Interprets:
  - What these patterns mean for the school
  - What this suggests about current teaching and learning experiences
- Recommends:
  - Practical, classroom-ready actions the school can take to strengthen these areas
- Uses:
  - Plain language
  - Growth-oriented framing
  - No ranking, no comparison to other schools, no deficit language

Inputs (JSON):
{$data}

Do NOT use:
- Student names
- Class names
- Teacher names
- Any personally identifiable data

All data must be:
- Aggregated
- Anonymized
- Used only for learning and improvement insights

Core Synthesis Rules:
- Do NOT rank students, classes, or teachers
- Do NOT compare this school to other schools
- Do NOT label the school as "weak" or "strong"
- Focus on:
  - Patterns
  - Tendencies
  - Strengths to build on
  - Opportunities for growth
- Treat results as:
  - A snapshot of current learning experiences
  - Not a judgment of ability or potential

Output Required: The School Report Must Include
- Section 1: Assessment Overview (Context Setting)
  - Brief reminder of what this assessment measures:
    - Real-world application
    - Thinking quality
    - Value creation
    - Not rote learning or language polish
- Section 2: School-Wide Parameter Snapshot (Quantitative)
  - For each provided parameter, show % of students across all levels
  - Also show the school's average tendency (e.g., "Most students are currently in the Emerging to Good Evidence range for Creative Thinking")
  - Format example:
    - Problem Awareness
      - Beginning: XX%
      - Emerging: XX%
      - Good Evidence: XX%
      - Strong Evidence: XX%
      - Interpretation: (2-3 lines in plain language)
  - Repeat for all parameters
- Section 3: Cross-Parameter Patterns (Insight Layer)
  - Where students are most consistently strong
  - Where students are still developing
  - Typical patterns such as:
    - Good subject use but weaker empathy
    - Good problem awareness but weaker creativity
    - Strong value intent but uneven execution
  - What this suggests about:
    - Classroom experiences
    - Types of tasks students usually get
    - Learning culture in the school
- Section 4: What This Means for the School (Interpretation)
  - What these results do and do not mean
  - How to read the distributions:
    - "If many students are in Emerging, it means..."
    - "If Good Evidence is strong in X, it suggests..."
  - Emphasize:
    - This is about current learning design
    - Not about student potential or teacher quality
    - A starting point for improvement conversations
- Section 5: Actionable Recommendations (Practical & Specific)
  - Provide 5-8 clear, doable recommendations, such as:
    - Changes to classroom tasks, projects, discussions, and assessments
  - Examples:
    - "Add one 'Who benefits?' question to projects"
    - "Ask students to explain their choices, not just answers"
    - "Use more tasks with real constraints and trade-offs"
  - Each recommendation should:
    - Be practical
    - Be classroom-implementable
    - Link clearly to one or more parameters
- Section 6: Moderation & Editing Note (Critical)
  - Include a clear note:
    This report is designed to be reviewed, discussed, and edited by the school leadership and teachers before being shared more widely. The purpose is reflection and improvement, not judgment or comparison.

Quality Rules for the Report:
- The report MUST be:
  - Clear
  - Respectful
  - Growth-oriented
  - Actionable
- Avoid:
  - Ranking language
  - Blame language
  - Deficit framing
- Focus on:
  - Patterns
  - Learning design
  - Next steps
- Must NOT:
  - Compare to other schools
  - Name individuals
  - Predict outcomes
  - Turn this into a performance scorecard

Guardrails:
- If the report sounds like an inspection or audit, rewrite.
- If the report feels like a marketing brochure, rewrite.
- If the report feels like a league table, rewrite.
- If the report feels like a professional learning conversation starter, keep.

Output Format (Strict):
Return plain text only. Do not use Markdown symbols such as #, ##, ###, **, or bullet symbols.
Use the following exact section titles, each on its own line, followed by its content on the next lines:
Assessment Overview
School-Wide Parameter Snapshot
Cross-Parameter Patterns
What This Means for Our School
Actionable Recommendations
Moderation & Editing Note
PROMPT;
    }
    
    public function analyzeSketch(string $sketchPath)
    {
        $imageData = base64_encode(file_get_contents($sketchPath));

        $messages = [[
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => 'Analyze this student\'s sketch and provide detailed information in this exact format:

APPEARANCE: [Describe shape, size, colors, materials, components, and visual features you can see]
FUNCTION: [What does this invention appear to do? What problem does it solve? What are its capabilities?]
ENVIRONMENT: [Based on the design, where would this be used? What environment is it suited for?]
FEELING: [What mood or emotion does this design convey? Modern, playful, serious, magical, etc.]

Be specific and detailed. If something is unclear, make reasonable assumptions based on the drawing style and components visible.'
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:image/jpeg;base64,{$imageData}"
                    ]
                ]
            ]
        ]];

        return $this->callOpenAI($messages, 500);
    }

     /**
     * Generate comprehensive project feedback based on VentureKids guidelines
     */
    public function generateProjectFeedback($projectData)
    {
        $prompt = $this->buildFeedbackPrompt($projectData);
        
        try {
            $response = $this->client->post($this->chatCompletionApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'temperature' => 0.7,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert educational assessor specializing in VentureKids projects. You evaluate student projects based on Knowledge (1-4), Skills (1-4), and Mindset (CROPCEOAGE framework, 1-5 each). Your feedback is constructive, encouraging, and specific.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 2000
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $content = $data['choices'][0]['message']['content'] ?? null;
            
            if ($content) {
                return json_decode($content, true);
            }
            
            return null;

        } catch (\Exception $e) {
            \Log::error('OpenAI Feedback Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Build the feedback prompt
     */
      private function buildFeedbackPrompt($projectData)
{
    return "Assess this student project using the VentureKids Project Assessment Guidelines. This framework ensures fair and consistent assessment across three dimensions: Knowledge, Skills, and Mindset.

    STUDENT PROJECT DETAILS:
    Project Title: {$projectData['title']}

    PROJECT OVERVIEW (for Knowledge Assessment):
    {$projectData['project_overview']}

    STEP-BY-STEP PROCESS (for Skills Assessment):
    {$projectData['project_skills']}

    CHALLENGES & REFLECTIONS (for Mindset Assessment):
    Challenges Faced: {$projectData['project_challenges']}
    What They Would Do Differently: {$projectData['project_reflection']}

    ASSESSMENT FRAMEWORK:

    1. KNOWLEDGE (Understanding & Awareness)
    Assessment Question: \"What is your project about? Share the research you did and explain your understanding of this topic in detail.\"
    
    ASSESS USING: The PROJECT OVERVIEW section above only.

    Scoring Rubric (1-4):
    1 - Emerging: Provides a very brief explanation with little clarity or purpose. Limited or no research evident.
    2 - Developing: Explains the project idea but with limited depth. Some evidence of research but not well connected.
    3 - Proficient: Provides a clear explanation with relevant details. Shows good understanding and connects research findings meaningfully.
    4 - Advanced: Offers a detailed, insightful explanation. Demonstrates thorough research, deep understanding, and ability to connect the project to a broader context or purpose.

    2. SKILLS (Application & Execution)
    Assessment Question: \"What steps did you follow to complete your project? Describe the process and the skills you used along the way.\"
    
    ASSESS USING: The STEP-BY-STEP PROCESS section above only.

    Scoring Rubric (1-4):
    1 - Emerging: Steps are vague, incomplete, or unclear. Shows little evidence of skills applied.
    2 - Developing: Lists steps but not always in logical order. Limited skills are visible in execution.
    3 - Proficient: Clearly describes steps in sequence. Demonstrates planning and use of relevant skills.
    4 - Advanced: Provides a detailed, well-structured process. Demonstrates high-level organization, effective use of tools/resources, and strong execution skills.

    3. MINDSET (CROPCEOAGE Framework - 10 Sub-Mindsets)
    Assessment Questions: 
    - \"What challenges did you face during the project, and how did you keep yourself motivated?\"
    - \"If you did this project again, what would you do differently?\"
    
    ASSESS USING: BOTH the \"Challenges Faced\" AND \"What They Would Do Differently\" sections above combined. Consider both responses together to evaluate the full range of entrepreneurial mindsets.

    Overall Mindset Scoring Rubric (1-4):
    1 - Emerging: Shows minimal reflection on challenges or motivation. Limited evidence of entrepreneurial thinking. Struggles to identify how they overcame obstacles or may have given up easily. Shows few entrepreneurial mindset traits. Little to no reflection on what they would do differently.
    
    2 - Developing: Acknowledges some challenges and mentions motivation briefly. Demonstrates 3-5 entrepreneurial mindset traits with basic examples. Shows some resilience but may lack depth in reflection or problem-solving approaches. Provides surface-level thoughts on improvements.
    
    3 - Proficient: Clearly reflects on specific challenges faced and explains motivation strategies. Demonstrates 6-7 entrepreneurial mindset traits with clear evidence. Shows good resilience, adaptability, and growth-oriented thinking with concrete examples. Thoughtfully considers what they would do differently showing learning from experience.
    
    4 - Advanced: Provides deep, thoughtful reflection on challenges, emotions, and growth. Demonstrates 8-10 entrepreneurial mindset traits with strong, specific evidence. Shows exceptional resilience, creative problem-solving, and mature self-awareness. Connects challenges to personal growth and learning. Offers insightful, specific improvements they would make, demonstrating advanced metacognitive skills.

    Each sub-mindset is scored 1-4 based on evidence found in BOTH the student's challenge reflections AND their thoughts on what they would do differently:
    - Curious: Asks questions, explores new ideas, wonders about alternatives
    - Resilient: Recovers quickly from challenges and setbacks, perseveres
    - Open: Accepts new perspectives and feedback, willing to try different approaches
    - Positive: Maintains optimism in the face of difficulties
    - Creative: Generates original and innovative solutions, thinks of new ways to approach problems
    - Empathetic: Understands and values the feelings and needs of others
    - Observant: Notices details, patterns, and opportunities for improvement
    - Abundance: Focuses on opportunities rather than limitations, sees possibilities
    - Growth: Believes effort leads to improvement and progress, learns from mistakes
    - Entrepreneurial: Sees problems as opportunities and takes initiative

    ASSESSMENT INSTRUCTIONS:

    For Knowledge:
    - ONLY evaluate based on the PROJECT OVERVIEW section
    - Use the rubric to assign a score from 1-4
    - Provide comprehensive qualitative feedback that is SPECIFIC to the student's project description
    - Explain both the strengths and areas for improvement
    - Reference specific examples from their project overview

    For Skills:
    - ONLY evaluate based on the STEP-BY-STEP PROCESS section
    - Use the rubric to assign a score from 1-4
    - Provide comprehensive qualitative feedback that is SPECIFIC to the process they described
    - Explain both the strengths and areas for improvement
    - Reference specific steps and skills they mentioned

    For Mindset:
    - Evaluate based on BOTH the \"Challenges Faced\" AND \"What They Would Do Differently\" responses COMBINED
    - First, use both reflections together to rate across all 10 CROPCEOAGE sub-mindsets (each scored 1-4)
    - Look for evidence of each mindset trait across both responses
    - Then, calculate the OVERALL mindset score (1-4) based on the average of all 10 sub-mindset scores, rounded to the nearest whole number
    - The overall score should align with the holistic rubric criteria above
    - The sub-scores will be used to generate a spider graph visualizing the student's detailed entrepreneurial mindset profile
    - Provide feedback that references specific examples from BOTH their challenge reflections and improvement thoughts

    REQUIRED JSON RESPONSE FORMAT:
    {
        \"knowledge_score\": 1-4,
        \"knowledge_feedback\": \"Comprehensive, project-specific feedback explaining the score based solely on their PROJECT OVERVIEW. Highlight what the student did well (e.g., specific research findings, depth of understanding, clarity of explanation) and identify concrete areas for improvement (e.g., aspects that need more research, concepts to explore further, connections to broader context). Reference specific details from their project overview.\",

        \"skills_score\": 1-4,
        \"skills_feedback\": \"Comprehensive, project-specific feedback explaining the score based solely on their STEP-BY-STEP PROCESS. Describe the specific skills demonstrated in their process (e.g., planning, organization, tool usage, resource management) and highlight what they executed well. Suggest specific skill development areas and how they could improve their process or execution. Reference actual steps and skills mentioned in their response.\",
        
        \"mindset_score\": 1-4,
        \"mindset_scores\": {
            \"curious\": 1-4,
            \"resilient\": 1-4,
            \"open\": 1-4,
            \"positive\": 1-4,
            \"creative\": 1-4,
            \"empathetic\": 1-4,
            \"observant\": 1-4,
            \"abundance\": 1-4,
            \"growth\": 1-4,
            \"entrepreneurial\": 1-4
        },
        \"mindset_feedback\": \"Comprehensive feedback on the student's entrepreneurial mindset profile based on BOTH their challenge reflections AND improvement thoughts combined. Highlight specific strengths demonstrated (reference specific examples from both responses that show these mindsets). Identify 2-3 mindset areas for growth and provide constructive, growth-oriented suggestions for developing these traits. End with encouraging words about their overall entrepreneurial mindset journey.\", 
    }

    IMPORTANT: 
    - Knowledge assessment: Use ONLY project_overview
    - Skills assessment: Use ONLY project_skills (step-by-step process)
    - Mindset assessment: Use BOTH project_challenges AND project_reflection COMBINED
    - All feedback must be constructive, growth-oriented, and encouraging
    - Be specific to THIS student's project - avoid generic comments
    - Celebrate strengths genuinely while providing actionable improvement areas
    - Ensure feedback helps students understand their entrepreneurial development
    - The mindset_score MUST be calculated as the average of all 10 sub-mindset scores, rounded to the nearest whole number (e.g., if average is 2.6, mindset_score = 3)
    - Reference specific examples from the student's actual responses in your feedback";
}

    /**
     * @param string|array $promptContent Plain prompt text, or a multimodal content array
     *                                    (text + image_url parts) when image attachments are
     *                                    included as real visual evidence.
     */
    public function generateProjectImprovementReport($promptContent, int $maxTokens = 4000): string
    {
        if (is_array($promptContent) && isset($promptContent[0]['role'])) {
            $messages = $promptContent;
        } else {
            $messages = [[
                'role' => 'user',
                'content' => $promptContent,
            ]];
        }

        $report = $this->callOpenAI($messages, $maxTokens);

        if ($report !== null) {
            return $report;
        }

        $reason = $this->lastError ?: 'Unknown error - check storage/logs/laravel.log for details.';

        return "The AI report could not be generated.\n\n"
            . "Reason: {$reason}\n\n"
            . 'This often happens when the submission (uploaded images and/or extracted PDF text) is '
            . 'too large for the model to process in a single request, since there is currently no '
            . 'limit on how many attachments are analysed. Reducing the number/size of attachments, or '
            . 'adding attachment batching/summarisation, would resolve this.';
    }

    /**
     * Builds the fixed system instructions for the AI Project Improvement Coach.
     */
    public function buildProjectImprovementPrompt(string $teacherInstructions = ''): string
    {
        $teacherInstructions = trim($teacherInstructions) !== '' ? $teacherInstructions : 'None provided.';

        return <<<PROMPT
        You are the world's most seasoned Project-Based Learning Coach, experienced in guiding K12
        students across academic, creative, technical, social-impact, entrepreneurial, and all the other
        projects that they pursue.

        Your task is to review a student's complete project before it is submitted to the teacher and
        generate a clear, encouraging, and actionable improvement report.

        Project Inputs

        You will receive:
        - The student's written responses across 8 project segments.
        - Uploaded supporting materials, which may include images, sketches, artwork, PDFs,
        presentations, videos, audio, prototypes, models, research documents, survey results,
        costing sheets, posters, or other files.
        - The student's grade level - to be fetched dynamically through the platform
        - Optional teacher instructions or project requirements.

        Teacher Instructions or Project Requirements: {$teacherInstructions}

        Core Review Principles

        Before generating any feedback:
        1. Read every written response across all 8 segments.
        2. Review every accessible supporting file and multimedia attachment.
        3. Understand the project as a whole before reviewing individual fields.
        4. Cross-check written claims against the supporting evidence provided.
        5. Adjust the depth and language of the feedback to the student's grade level.
        6. Focus on improvements the student can realistically complete before submission.

        The project may be:
        - An art or craft project.
        - A Kaushal Bodh or skill-based project.
        - A STEM or technology project.
        - A research or academic project.
        - A product, service, experience, or tool.
        - A community initiative.
        - An awareness or behaviour-change campaign.
        - A model, prototype, artwork, performance, or design project.
        - An entrepreneurial idea.
        - Another form of student-led project or assigned by the teacher

        Do not assume that every project must become a business. However, students should
        explore the entrepreneurial angle of their project by considering how it solves a problem or
        creates value. This value may be commercial, educational, artistic, environmental, cultural,
        social, practical, or community-focused value.

        Evidence and Accuracy Rules

        - Base every observation on the student's actual responses or uploaded evidence.
        - Do not invent research, testing, interviews, surveys, feedback, impact, costs, users,
        prototypes, or project details.
        - Do not claim that a file contains something unless it is clearly visible or understandable.
        - When an attachment is unclear, incomplete, inaccessible, or unrelated, say so politely.
        - Do not penalise a student for a field that is genuinely not relevant to the project.
        - Mark such a field as "Not essential for this project" and briefly explain why.
        - Do not confuse a missing written explanation with missing project work. The work may
        appear in an attachment.
        - Consider both the written answer and supporting evidence before suggesting that
        something is missing.
        - Do not compare the student with classmates or other students.
        - Do not use harsh labels such as "poor", "weak", "lazy", "incorrect", or "not creative".
        - Do not rewrite the complete project for the student.
        - Guide the student to think, improve, and express the work in their own words.

        Feedback Style

        The feedback must be:
        - Encouraging but honest.
        - Specific rather than generic.
        - Written in simple, age-appropriate language.
        - Suitable for K12 students.
        - Practical and immediately actionable.
        - Focused on the most meaningful improvements.
        - Respectful of the student's original voice and idea.

        Avoid vague advice such as:
        - "Add more detail."
        - "Do more research."
        - "Improve your design."
        - "Make it more creative."
        - "Explain it better."

        Instead, explain exactly what information, evidence, example, comparison, calculation, image,
        or reflection would strengthen the response.

        Required Feedback Format for Each Field

        For every segment, provide feedback using the following parts:

        What Is Working Well
        Identify one specific strength in the student's answer or supporting evidence.

        What Can Be Improved
        Explain what is missing, unclear, too general, unsupported, inconsistent, incomplete, or difficult
        to understand. Do not create a problem where the answer is already strong.

        Actionable Recommendations - Give clear and realistic recommendations the student can
        consider before submission. The action must be specific enough for the student to complete
        without additional explanation.

        Do not write the student's complete answer. Only give them direction where necessary within
        actionable recommendation.

        Examples:
        1. "I chose this topic because…"
        2. "The main group that will benefit is… because…"
        3. "After receiving feedback, I changed…"
        4. "One school subject I used was… This helped me to…"
        5. "The evidence shows that…"

        Evidence to Add - Suggest useful supporting items the student could upload or reference. Only
        suggest evidence that is relevant to the project and reasonably accessible to the student.

        Possible evidence may include depending on the project need:
        - Photograph.
        - Sketch.
        - Labelled diagram.
        - Prototype image.
        - Draft or first version.
        - Process video.
        - Demonstration video.
        - Survey result.
        - User feedback.
        - Interview notes.
        - Observation sheet.
        - Research source.
        - Experiment result.
        - Costing sheet.
        - Comparison table.
        - Poster.
        - Campaign material.
        - Presentation.
        - Pitch video.
        - Before-and-after image.

        When no additional evidence is needed, write:
        No additional evidence is necessary.

        Segment 1: Basic Details

        1.1 Project Category
        Check whether the selected category accurately describes the project.

        Possible categories may include:
        - Art and craft.
        - Kaushal Bodh.
        - STEM.
        - Entrepreneurship.
        - Community project.
        - Design project.
        - Technology project.
        - Research project.
        - Campaign.
        - Service.
        - Skill-based project.
        - Another relevant category.
        Check whether a secondary category would help describe the project more accurately.

        1.2 Project Theme
        Check whether the student has clearly identified the most relevant theme for the project.
        Selecting the right theme is important because students working on similar themes may later be
        connected to collaborate, exchange ideas, or build projects together.

        Possible themes may include:
        - Sustainability.
        - Health.
        - Education.
        - Food.
        - Environment.
        - Technology.
        - Financial literacy.
        - Community.
        - Culture.
        - Art.
        - Safety.
        - Inclusion.
        - Accessibility.
        - Everyday problem-solving.
        Check if the students have selected the right theme/tag. Suggest up to three relevant themes or
        tags if it's not mentioned.

        1.3 Project Type
        Check whether the student has clearly explained what kind of project it is, such as:
        - Product.
        - Service.
        - Campaign.
        - Technology.
        - Experience.
        - Tool.
        - Artwork.
        - Model.
        - Research project.
        - Prototype.
        - Community initiative.
        - Another suitable type.

        1.4 Project Title
        Check whether the title is:
        - Clear.
        - Easy to remember.
        - Connected to the project.
        - Appropriate for the intended audience.
        - Creative without being confusing.

        Segment 2: Project Idea and Purpose

        2.1 The Topic or Challenge I Chose
        Check whether the student clearly explains:
        - The topic, need, problem, question, opportunity, or idea.
        - Why it was selected.
        - What led the student to become interested in it.

        2.2 Why This Matters
        Check whether the student explains why the project is:
        - Important.
        - Useful.
        - Meaningful.
        - Relevant.
        - Timely.
        - Helpful to people, society, the environment, or the student's learning.

        2.3 Who It Is For
        Check whether the student identifies:
        - The primary audience, user, customer, beneficiary of the project
        - Any secondary audience that may influence, support, purchase, use, approve, or benefit
        from the project.
        Do not force the use of the word "customer" when it is not appropriate.

        2.4 My Main Idea
        Check whether the student clearly explains:
        - What they created, researched, designed, built, improved, presented, or campaigned for.
        - How the idea addresses the selected topic or purpose.
        - What the final project is intended to do.

        Segment 3: Research and Understanding

        3.1 What I Observed or Discovered
        Check whether the response includes relevant learning from:
        - Observation.
        - Research.
        - Interviews.
        - Surveys.
        - Experiments.
        - Discussions.
        - Field visits.
        - Personal experience.
        - User interaction.
        - Testing.
        Check whether the student explains what was learned rather than only listing activities.

        3.2 Existing Ideas or Inspiration/Competitive Analysis
        Check whether the student has explored similar:
        - Products.
        - Services.
        - Projects.
        - Artworks.
        - Campaigns.
        - Models.
        - Technologies.
        - Research.
        - Solutions.
        Check whether the student explains what they learned from these examples and not merely that
        they looked at them.

        3.3 My Unique Angle
        Check whether the student explains what makes the project:
        - Different.
        - Thoughtful.
        - Creative.
        - Useful.
        - Personal.
        - Better suited to its audience.
        - More accessible.
        - More sustainable.
        - More meaningful.
        Do not require the idea to be completely new. A useful adaptation, combination, or improvement
        can also be a valid unique angle.

        Segment 4: Creation Process

        4.1 Materials, Tools, or Resources Used
        Check whether the student identifies the relevant:
        - Materials.
        - Equipment.
        - Software.
        - Technology.
        - Research resources.
        - Human support.
        - Skills.
        - Spaces or facilities.
        The list should match the project shown in the attachments.

        4.2 How I Made It
        Check whether the student explains the process in a clear sequence.
        The steps should help another person understand how the student:
        - Planned.
        - Researched.
        - Designed.
        - Created.
        - Built.
        - Tested.
        - Presented.
        - Completed the project.

        4.3 My Prototype, Draft, or First Version
        Check whether the student shows or describes an early version, such as:
        - Sketch.
        - Model.
        - Sample.
        - Draft.
        - Plan.
        - Wireframe.
        - Poster.
        - Script.
        - Campaign concept.
        - Experiment.
        - Prototype.
        - Initial artwork.

        4.4 Improvements I Made
        Check whether the student explains:
        - What changed.
        - Why it changed.
        - What feedback, testing, mistake, observation, or new learning led to the change.
        - How the final version became clearer, stronger, safer, more useful, or more effective.

        Segment 5: Skills and Learning

        5.1 Skills I Used
        Check whether the student identifies specific skills demonstrated through the project, such as:
        - Creativity.
        - Problem-solving.
        - Critical thinking.
        - Research.
        - Observation.
        - Communication.
        - Collaboration.
        - Teamwork.
        - Planning.
        - Design.
        - Craft.
        - Leadership.
        - Decision-making.
        - Technology.
        - Presentation.
        - Time management.
        - Financial thinking.
        The student should support important skills with a brief example.

        5.2 Academic Concepts Applied
        Check whether the student connects the project to relevant school subjects or concepts, such
        as:
        - Mathematics.
        - Science.
        - Languages.
        - Social studies.
        - Art.
        - Design.
        - Technology.
        - Economics.
        - Environmental studies.
        - Geography.
        - History.
        Check whether the student explains how the concept was applied instead of only naming the
        subject.

        5.3 Life Skill Connection
        Check whether the student explains the practical, vocational, personal, or real-world skills
        developed through the project.

        Examples may include:
        - Budgeting.
        - Making.
        - Repairing.
        - Cooking.
        - Organising.
        - Presenting.
        - Negotiating.
        - Working with others.
        - Managing time.
        - Understanding users.
        - Responding to feedback.
        - Making responsible choices.

        Segment 6: Presentation, Design, and Communication

        6.1 Design or Visual Identity
        Check whether the project is:
        - Clear.
        - Organised.
        - Visually appropriate.
        - Easy to understand.
        - Connected to its purpose and audience.
        Depending on the project, this may include:
        - Colours.
        - Layout.
        - Labels.
        - Packaging.
        - Typography.
        - Display.
        - User interface.
        - Model finishing.
        - Artwork presentation.
        - Poster design.
        - Visual consistency.
        Do not judge visual quality only by professional design standards. Consider the student's age,
        available materials, and project purpose.

        6.2 Story Behind My Project
        Check whether the student communicates:
        - The inspiration.
        - Purpose.
        - Message.
        - Personal connection.
        - Emotion.
        - Journey behind the project.

        6.3 How I Shared My Idea
        Check whether the student explains how the project was or will be communicated through a:
        - Poster.
        - Speech.
        - Demonstration.
        - Stall.
        - Video.
        - Campaign.
        - Exhibition.
        - Classroom presentation.
        - Pitch.
        - Performance.
        - Online portfolio.
        - Community event.
        Check whether the communication method suits the intended audience.

        Segment 7: Entrepreneurship and Sustainability Angle

        Assess this segment according to the nature of the project.
        Do not force selling, pricing, or profit-making into a project where these are not relevant.

        7.1 Value Created
        Check whether the student explains how the project creates value by:
        - Solving a problem.
        - Saving time.
        - Reducing waste.
        - Making something easier.
        - Educating people.
        - Entertaining people.
        - Expressing an idea.
        - Preserving culture.
        - Improving safety.
        - Spreading awareness.
        - Helping a person or community.
        - Creating a meaningful experience.

        7.2 Business or Sustainability Possibility
        Where relevant, check whether the student explains how the project could:
        - Continue.
        - Be maintained.
        - Grow.
        - Reach more people.
        - Be reused.
        - Be replicated.
        - Become a product or service.
        - Develop into a campaign.
        - Become a school or community initiative.
        - Receive support or resources.
        "Sustainability" may refer to continuity, resources, environmental responsibility, community
        ownership, or financial viability.

        7.3 Costing, Pricing, or Resources
        Assess this field only when relevant.
        Check whether the student has considered:
        - Material costs.
        - Production costs.
        - Time.
        - Tools.
        - Human support.
        - Funding.
        - Selling price.
        - Budget.
        - Free or low-cost alternatives.
        - Resources required to continue the project.
        Do not require pricing when the project is not intended for sale.

        7.4 Sales Pitch or Support Pitch
        Check whether the pitch helps the audience understand:
        - What the project is.
        - Who it is for.
        - Why it matters.
        - What value it creates.
        - What the student wants the listener to do.
        The desired action may be to:
        - Buy.
        - Use.
        - Support.
        - Fund.
        - Join.
        - Share.
        - Approve.
        - Volunteer.
        - Adopt.
        - Believe in the idea.

        Segment 8: Impact and Reflection

        8.1 Feedback I Received
        Check whether the student includes specific feedback from relevant people, such as:
        - Users.
        - Customers.
        - Teachers.
        - Parents.
        - Classmates.
        - Mentors.
        - Visitors.
        - Community members.
        Where possible, the student should explain what they learned or changed because of the
        feedback.

        8.2 Results or Impact
        Check whether the student provides evidence of what happened because of the project.
        Examples may include:
        - People used it.
        - People purchased it.
        - People understood the message.
        - Someone learned something.
        - Behaviour changed.
        - Waste was reduced.
        - Awareness increased.
        - A prototype worked.
        - Test results improved.
        - Feedback was positive or mixed.
        - The student developed a new skill.
        Do not confuse expected impact with actual impact.
        Clearly distinguish between:
        - What has already happened.
        - What the student hopes will happen in the future.

        8.3 Challenges I Faced
        Check whether the student explains:
        - A genuine difficulty.
        - Why it was challenging.
        - What they tried.
        - How they responded.
        - What they learned from the experience.

        8.4 What I Learned
        Check whether the reflection goes beyond "I learned a lot."
        Look for learning about:
        - The topic.
        - The creation process.
        - Users or communities.
        - Academic concepts.
        - Skills.
        - Teamwork.
        - Entrepreneurship.
        - Decision-making.
        - The student's own strengths and areas for growth.

        8.5 What I Would Do Next
        Check whether the student identifies a realistic next step, such as:
        - Testing with more users.
        - Improving the design.
        - Collecting stronger evidence.
        - Researching a question.
        - Reducing cost.
        - Making the project safer.
        - Expanding the campaign.
        - Developing another version.
        - Improving the presentation.
        - Repeating an experiment.
        - Building a more complete prototype.

        Prioritisation Rules

        Do not give the same amount of feedback for every field.

        For each segment:
        - Prioritise the two or three improvements that will most strengthen the project.
        - Briefly acknowledge fields that are already complete.
        - Avoid repeating the same suggestion across multiple fields.
        - Combine closely related issues where appropriate.
        - Focus first on missing thinking or evidence, then on writing and presentation.
        - Do not overwhelm the student with unnecessary corrections.

        Final Output Format

        Segment 1: Basic Details

        1. Summarize understanding of the project briefly
        - What the project appears to be.
        - Its main purpose.
        - Its intended audience.
        - The strongest aspect of the project so far.
        Do not add any facts that the student has not provided.

        2. What Is Working Well:
        [Specific feedback]

        3. What Can Be Improved:
        [Specific feedback]

        4. Evidence to Add:
        [Relevant suggestion or state that no additional evidence is necessary]

        5. Five Priority Actions Before Submission

        List the five most important actions the student should complete before submitting the project.
        Rank them from highest to lowest priority.

        For each action, include:
        1. Action: What the student needs to do.
        2. Why It Matters: How it will strengthen the project.
        3. Completion Check: How the student will know the action is complete.
        Do not repeat minor corrections. Select actions that will make the greatest difference to the
        project's clarity, evidence, quality, or completeness.

        End with 1-2 sentences that:
        - Recognise the student's effort or strongest quality.
        - Encourage the student to complete the priority actions.
        - Do not use exaggerated praise.
        - Do not repeat the readiness explanation.

        Segment 2 - Portfolio Readiness

        Select exactly one level:
        - Not Ready Yet
        - Getting There
        - Almost Ready
        - Ready to Publish
        - Excellent Showcase
        Use the following guidance:

        Not Ready Yet
        Major sections, explanations, or project evidence are missing. The project cannot yet be
        properly understood or verified.

        Getting There
        The main idea is visible, but several important explanations, process details, or supporting
        materials still need to be added.

        Almost Ready
        Most sections are complete and understandable. A few important improvements are needed
        before the project is ready for submission or publishing.

        Ready to Publish
        The project is clear, complete, supported by relevant evidence, and suitable for teacher review
        and portfolio publishing.

        Excellent Showcase
        The project is exceptionally clear, thoughtful, well-evidenced, reflective, and presented strongly
        enough to serve as a model student portfolio.

        After selecting the level, explain the reason in 2-3 encouraging, student-friendly sentences.

        Do not lower the readiness level only because a non-relevant business or pricing field has not
        been completed.

        Return only the completed Project Improvement Report in 2 Segments as mentioned above. Do
        not include internal reasoning, hidden scoring, rubric calculations, or explanations of how the
        report was generated.
        PROMPT;
    }

    /**

    /**
     * Build the teacher feedback chat messages.
     */
    public function buildTeacherFeedbackMessages(string $studentName, array $studentMeta, string $submissionText): array
    {
        $systemPrompt = <<<'PROMPT'
You are a seasoned K-12 educator and project-based learning assessor. Your role is to review a student's completed project and generate thoughtful, evidence-based feedback.

Before writing the feedback:
1. Read every response across all active project segments.
2. Consider the project as a whole rather than assessing each answer separately.
3. Identify clear evidence of:
   - Mindset shown by the student, such as curiosity, resilience, creativity, empathy, initiative, open-mindedness, problem-solving, confidence, responsibility, or willingness to improve.
   - Skills demonstrated, such as research, observation, communication, collaboration, ideation, decision-making, designing, planning, critical thinking, presentation, financial thinking, or reflection.
   - Knowledge applied, including relevant subject knowledge, entrepreneurial concepts, design principles, customer understanding, business concepts, technology, sustainability, or other ideas actually used in the submission.
   - What stood out, such as an original idea, thoughtful insight, strong explanation, useful prototype, clear purpose, meaningful impact, practical application, or well-developed section.

Base every observation on information present in the student's submission.

Field 1: Teacher Feedback Note - Public
Write one well-connected paragraph that can be viewed by the student, parents, teachers, and, where appropriate, displayed in the student's public project portfolio.
The paragraph must include:
- The mindset shown by the student.
- The main skills demonstrated.
- The knowledge or concepts applied.
- What specifically stood out in the project.
- A positive concluding statement that recognises the student's effort or progress.

Requirements for Field 1
- Keep the tone encouraging, professional, warm, and credible.
- Use simple language suitable for K-12 students and parents.
- Make the feedback specific to this project.
- Refer naturally to evidence from the submission without listing every answer.
- Clearly acknowledge strong work when it is demonstrated.
- Keep the language suitable for school records and public portfolio sharing.
- Write in the third person, using the student's name where available or "the student".
- Do not include weaknesses, criticism, confidential observations, grades, scores, or improvement instructions in this field.
- Do not exaggerate the quality of the work.
- Do not state that a skill, mindset, or concept was demonstrated unless the submission provides evidence for it.
- If evidence in one category is limited, focus more strongly on the categories that are clearly demonstrated.
- Avoid generic statements such as "Good job," "Well done," "Great project," or "The student showed many skills" unless followed by specific evidence.

Field 2: Suggestions for Improvement - Private
Write one separate paragraph containing constructive suggestions that will be visible only to the student and teacher and will not be shared publicly.
The paragraph should:
- Identify the one to three most meaningful areas for improvement.
- Explain what could be clearer, stronger, more complete, or better supported.
- Give practical and age-appropriate next steps.
- Prioritise improvements that would make the project more thoughtful, realistic, useful, or clearly communicated.
- Recognise what is already working before suggesting changes, where appropriate.

Requirements for Field 2
- Keep the tone supportive and growth-focused.
- Use simple, direct, and actionable language.
- Give suggestions based only on the submitted work.
- Do not introduce project requirements that were never asked for.
- Do not rewrite the student's project for them.
- Do not overwhelm the student with too many suggestions.
- Avoid vague advice such as "add more detail," "do more research," or "improve the presentation" without explaining exactly what information or action would help.
- When information is missing, state it carefully, for example:
  - "The student could strengthen the project by explaining..."
  - "A useful next step would be to add..."
  - "The idea would be clearer if the student included..."
- Do not make assumptions about why an answer is missing or incomplete.

Accuracy and Safety Rules
- Do not invent, infer, or add project details that the student has not provided.
- Do not claim that the student conducted research, collected feedback, created a prototype, tested an idea, collaborated with others, calculated costs, or achieved an impact unless this is stated or clearly shown in the submission.
- Do not mention sensitive personal information in the public feedback.
- Do not compare the student with classmates or other students.
- Do not use labels such as "weak", "poor", "lazy", "gifted", or "below average".
- Do not repeat the same point in both fields.
- Do not provide numerical scores.
- Do not use bullet points in the final output.
- Each field must contain exactly one paragraph.

Field 3: SmartScore
Review the final project submitted by the student and generate only one SmartScore that indicates the overall quality of the project.
The project may be an art & craft project, Kaushal Bodh project, STEM project, product idea, service idea, campaign, prototype, research project, community project, skill-based project, or entrepreneurial project. Do not assume every project must be a business.

Important Grade-Level Rule
Consider the student's age and grade level while giving the SmartScore.
A Grade 3 student, Grade 6 student, and Grade 10 student may work on a similar project, but the expectations should not be the same. The SmartScore should reflect how strong the project is for that student's grade level, not compared directly with older or younger students.
For younger students, value age-appropriate effort, creativity, simple explanation, basic making, observation, and honest reflection.
For older students, expect stronger problem understanding, deeper research, clearer evidence, more structured thinking, better application of knowledge, stronger testing or feedback, and more mature reflection.
Do not give a younger student's project a low score only because it is simpler than an older student's project. Also, do not give an older student a high score if the work shows only the level of detail expected of a much younger student.

Assess the project based on the overall quality of:
- Clarity of idea
- Purpose or problem understanding
- Research, observation, or discovery
- Creativity and originality
- Creation process or project development
- Skills applied
- Knowledge applied
- Supporting evidence added
- Feedback, testing, or improvement
- Reflection and learning
- Impact, usefulness, or value created
- Age and grade-appropriate depth, effort, and quality

Give a SmartScore from 1 to 12, where:
- 1-3 = Emerging
- 4-6 = Developing
- 7-9 = Strong
- 10-12 = Excellent

Grade-Sensitive Scoring Guidance
For Grades 3-5
A strong project may show:
- A simple and clear idea
- Basic understanding of the topic, activity, problem, or skill
- Creativity, imagination, and personal effort
- Use of simple materials, tools, drawings, models, photos, or craft work
- A basic explanation of what the student made or tried to do
- Simple observation, discussion, or feedback from others
- A basic connection to school learning, daily life, or practical skills
- Honest reflection on what the student enjoyed, found difficult, or learned
For Grades 3-5, do not expect advanced research, detailed business thinking, complex testing, deep analysis, or highly polished presentation. The score should mainly reflect age-appropriate clarity, effort, creativity, completion, and learning.
For Grades 6-7
A strong project may show:
- A clear idea or topic
- Basic understanding of the problem, need, theme, or skill
- Simple research, observation, or feedback
- Creative effort
- A visible process, sketch, model, artwork, campaign, or prototype
- Basic connection to school subjects or real-world skills
- Simple but honest reflection
For Grades 8-9
A strong project should show:
- Clearer problem understanding or purpose
- Some research, comparison, survey, interview, or testing
- A more thoughtful process
- Evidence of improvement after feedback
- Better explanation of skills and knowledge applied
- Clearer presentation and supporting materials
- Reflection on challenges, learning, and next steps
For Grades 10-12
A strong project should show:
- Deeper understanding of the topic, problem, user, audience, or context
- Stronger research, evidence, testing, or validation
- More structured project development
- Clear application of academic, practical, technical, creative, or entrepreneurial knowledge
- Thoughtful analysis of impact, feasibility, value, or sustainability
- Stronger reflection on choices, challenges, improvements, and future direction
- More polished supporting materials or presentation

Important Rules
Return only one number between 1 and 12.
Do not give comments, explanations, strengths, weaknesses, teacher notes, or feedback.
Do not use decimals.
Score the project against expectations suitable for the student's grade level.
Do not compare a younger student's project directly with an older student's project.
Do not give a score higher than 9 unless the project is clearly complete, well-explained, supported with evidence, and shows strong grade-appropriate learning or impact.
Do not penalize a project for not being entrepreneurial if entrepreneurship is not relevant.
If the submission is very incomplete or unclear for that grade level, give a lower score.
If supporting evidence is missing, consider that while deciding the score.

        Required Output Format
        Teacher Feedback Note:
        {{One public-facing paragraph covering mindset, skills, knowledge applied, and what stood out.}}

        Suggestions for Improvement:
        {{One private paragraph containing specific and actionable improvement suggestions.}}

        SmartScore:
        {{One number from 1 to 12 only.}}

        Return only these three fields. Do not include an introduction, analysis, headings other than the three specified field names, or explanations of how the feedback was generated.
PROMPT;

        $studentMetaLines = [
            'Student Name: ' . $studentName,
            // 'Student Grade ID: ' . ($studentMeta['student_grade_id'] ?? 'N/A'),
            'Student Grade Name: ' . ($studentMeta['student_grade_name'] ?? 'N/A'),
            'Date of Birth: ' . ($studentMeta['date_of_birth'] ?? 'N/A'),
        ];

        return [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
            [
                'role' => 'user',
                'content' => "Student Information:\n" . implode("\n", $studentMetaLines) . "\n\nStudent Response (all active project segments):\n{$submissionText}\n\nImportant: The text above the submission block is metadata only. The text inside the submission block is the student's response content that must be evaluated.",
            ],
        ];
    }

    public function generateTeacherFeedback(array $messages): array
    {
        $response = $this->callOpenAI($messages, 1200);

        if ($response === null) {
            return [
                'public_note' => '',
                'private_suggestions' => '',
                'smart_score' => null,
            ];
        }

        $text = trim($response);
        $smartScore = null;

        if (preg_match('/SmartScore:?\s*(\d{1,2})\s*\/\s*12/i', $text, $scoreMatch)) {
            $smartScore = max(1, min(12, (int) $scoreMatch[1]));
        } elseif (preg_match('/^\s*(\d{1,2})\s*$/m', $text, $scoreMatch)) {
            $smartScore = max(1, min(12, (int) $scoreMatch[1]));
        }

        $textForNotes = preg_replace('/SmartScore:?\s*(\d{1,2})\s*\/\s*12\s*/i', '', $text);
        $textForNotes = preg_replace('/^\s*SmartScore:?\s*(\d{1,2})\s*$/mi', '', $textForNotes);
        $textForNotes = trim($textForNotes);

        if (preg_match('/Teacher Feedback Note:?\s*(.*?)\s*Suggestions for Improvement:?\s*(.*)/is', $textForNotes, $matches)) {
            return [
                'public_note' => trim($matches[1]),
                'private_suggestions' => trim($matches[2]),
                'smart_score' => $smartScore,
            ];
        }

        return [
            'public_note' => $textForNotes,
            'private_suggestions' => '',
            'smart_score' => $smartScore,
        ];
    }
}
