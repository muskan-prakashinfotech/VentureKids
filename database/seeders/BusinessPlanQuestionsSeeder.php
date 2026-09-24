<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class BusinessPlanQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          $now = now();

        $questions = [
        [
            'question_value' => "Ready to turn your awesome idea into a real business plan?",
            'prompt_text' => "Hey, young entrepreneur! I'm VentureKids Bot, your business buddy.",
            'question_type' => 'radio',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => null,
            'step' => 1,
            'display_order' => 1,
            'options' => [
                    ['option_text' => " Yes, let's go!", 'option_value' => 'yes','business_plan_id'=>1,],
                    ['option_text' => " Not now", 'option_value' => 'no','business_plan_id'=>1,],
            ],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "What's one problem you want to focus on?",
            'prompt_text' => "Let's begin with what problem you want to solve. Observe problems in your school, home, or community? A problem is something people struggle with.",
            'question_type' => 'textarea',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => "Students often feel bored in class because the lessons are not interactive",
            'step' => 2,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "What is the solution you are offering?",
            'prompt_text' => "Now… How will you help solve that problem? Think of a product or service that can help.",
            'question_type' => 'textarea',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => "To encourage kids to eat healthy, you can design a digital fruit tracker that rewards them with badges for eating fruits.",
            'step' => 3,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "What is unique or special about your idea?",
            'prompt_text' => "Think about…how is your solution unique or different from others?",
            'question_type' => 'textarea',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => "To help kids stay hydrated, I'll sell water bottles with fun reminders that glow or beep when it's time to drink.",
            'step' => 4,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "How many items you plan to sell in 1 year?",
            'prompt_text' => "Let's set some goals! What's your BIG goal for this business in 1 year? Make sure you have a SMART Goal. (S)pecific, (M)easurable, (A)chievable, (R)elevant, (T)ime-bound",
            'question_type' => 'text',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => "Sell 120 products in 1 Year",
            'step' => 5,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "Describe what you're offering and explain how it works.",
            'prompt_text' => "Detail out your product or service! What does it look like, what's it made of, how does it work?",
            'question_type' => 'textarea',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => "I sell handmade slime kits for kids that come with pre-measured ingredients, glitter, and a fun surprise toy inside. Each kit has a special theme like “Galaxy Slime” or “Ice Cream Slime.”It takes just 10 minutes to make and is perfect for parties or gifts.",
            'step' => 6,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "Who needs or wants your solution the most?",
            'prompt_text' => "Let's figure out who your ideal audience is! Who are your customers, and what kind of people would be most excited to use or buy what you are offering?",
            'question_type' => 'textarea',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => "Teenagers, Parents, Teachers and Pet lovers",
            'step' => 7,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "What is the cost per unit?",
            'prompt_text' => "Let's figure out how would you price your solution.Think about how much do you need to spend on materials to make one product or offer your service once?",
            'question_type' => 'text',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => "For one tie-dye t-shirt, what is the total cost of the colors and the shirt?",
            'step' => 8,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "Calculate the time it takes to make 1 unit or offer your service once and how much you think your time should cost.",
            'prompt_text' => null,
            'question_type' => 'text',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' =>'If it takes you 1 hour to finish, how much is your time worth?',
            'step' => 8,
            'display_order' => 2,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "How much profit you want to make per unit?",
            'prompt_text' => null,
            'question_type' => 'text',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' =>"  How much would you like to earn as profits?",
            'step' => 8,
            'display_order' => 3,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "Think about your audience: Where do they spend time, and what kind of message would excite them about your idea? Give as many details as possible?",
            'prompt_text' => "Now let's tell the world! What message do you want to share with your audience? Write your communication clearly and engagingly. Also, choose platforms where your audience spends most of their time.",
            'question_type' => 'textarea',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' =>"If your audience spends a lot of time on Instagram and enjoys motivational reels, consider creating your reels with an inspiring message that also showcases what you offer. You can even team up with popular influencers your audience follows to help spread the word.",
            'step' => 9,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "Apart from making your product, what other things do you spend money on? Let's list those fixed or extra costs.",
            'prompt_text' => "Let's do some money magic and help you create financial projections! Revenue is all the money you get. Gross Profit is the money you make after paying to create your product or service, but before you pay for other fixed costs like rents, ads, etc. Net profit is what's left after everything is paid for, including rent, ads, etc.",
            'question_type' => 'textarea',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => null,
            'step' => 10,
            'display_order' => 1,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "Based on the above provide total fixed cost for 1 year",
            'prompt_text' => null,
            'question_type' => 'text',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => null,
            'step' => 10,
            'display_order' => 2,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'question_value' => "By what percentage will the business grow every year?",
            'prompt_text' => null,
            'question_type' => 'text',
            'is_required' => true,
            'business_plan_id'=>1,
            'placeholder_text' => null,
            'step' => 10,
            'display_order' => 3,
            'options' => [],
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ];

        foreach ($questions as $question) {
            $questionId = DB::table('business_plan_questions')->insertGetId([
                'question_value' => $question['question_value'],
                'prompt_text' => $question['prompt_text'],
                'question_type' => $question['question_type'],
                'is_required' => $question['is_required'],
                'business_plan_id'=>$question['business_plan_id'],
                'placeholder_text' => $question['placeholder_text'] ?? null,
                'step' => $question['step'],
                'display_order' => $question['display_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($question['options'] as $option) {
                DB::table('business_plan_question_options')->insert([
                    'business_plan_question_id' => $questionId,
                    'option_text' => $option['option_text'],
                    'option_value' => $option['option_value'],
                    'business_plan_id'=>$option['business_plan_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
