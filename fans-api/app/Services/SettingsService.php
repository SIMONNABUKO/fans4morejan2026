<?php

namespace App\Services;

use App\Models\User;
use App\Models\SettingCategory;
use App\Models\UserSetting;

class SettingsService
{
    public function getUserSetting(User $user, string $category, string $key, $default = null)
    {
        $setting = $user->settings()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('name', $category);
            })
            ->where('key', $key)
            ->first();

        return $setting ? $setting->value : $default;
    }

    public function setUserSetting(User $user, string $category, string $key, $value)
    {
        $categoryModel = SettingCategory::firstOrCreate(['name' => $category]);

        return $user->settings()->updateOrCreate(
            ['setting_category_id' => $categoryModel->id, 'key' => $key],
            ['value' => $value]
        );
    }

    public function getUserSettings(User $user, string $category)
    {
        return $user->settings()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('name', $category);
            })
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }
}

