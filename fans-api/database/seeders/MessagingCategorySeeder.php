<?php
// database/seeders/MessagingCategorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SettingCategory;

class MessagingCategorySeeder extends Seeder
{
    public function run()
    {
        SettingCategory::firstOrCreate([
            'name' => 'messaging',
        ], [
            'description' => 'Message settings and preferences'
        ]);
    }
}