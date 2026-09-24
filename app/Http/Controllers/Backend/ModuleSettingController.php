<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ModuleSetting;

use Illuminate\Http\Request;


class ModuleSettingController extends Controller
{
    public function updateDailyQuizSatus(Request $request)
    {
        ModuleSetting::updateOrCreate(
            ['key' => 'daily_quiz_enabled'],
            ['value' => $request->has('daily_quiz_enabled')]
        );

        return back()->with('message', 'Daily Quiz setting updated successfully.');
    }
}
