<?php

namespace App\Services;

use App\Models\ModuleSetting;

class ModuleSettingService
{
    public function isModuleEnabled(string $key): bool
    {
        return ModuleSetting::where('key', $key)->value('value') == 1;
    }
}
