<?php

namespace App\Helpers;
use App\Models\Students;

class CommonHelper
{
    public static function getRecipientEmailByUserId(int $userId, bool $sendToSchool = true)
    {
        // Load student with user and school
        $student = Students::with([
                        'user:id,email',     
                        'school:id,official_email_id'    
                    ])
                    ->where('user_id', $userId)
                    ->first();

        if (!$student) {
            return null; 
        }

        // 1. Student email
        if (!empty($student->user->email)) {
            return $student->user->email;
        }

        // 2. Parent email
        if (!empty($student->parent_email)) {
            return $student->parent_email;
        }

        // 3. School email
        if ($sendToSchool && !empty($student->school->official_email_id)) {
            return $student->school->official_email_id;
        }

        // 4. Nothing found
        return null;
    }
}