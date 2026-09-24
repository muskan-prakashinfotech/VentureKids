<?php

namespace App\Helpers;

use App\Models\LoginTracking;
use App\Enums\UserType;

class LoginHelper
{
    public static function track(UserType $userType, int $itemId): void
    {
        LoginTracking::create([
            'user_type' => $userType->value,
            'item_id'   => $itemId,
            'login_at'  => now(),
        ]);
    }
}