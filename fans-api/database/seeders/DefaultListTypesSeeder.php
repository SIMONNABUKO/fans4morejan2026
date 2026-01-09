<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ListType;

class DefaultListTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLists = [
            'VIP',
            'Followers',
            'Following',
            'Subscribed',
            'Blocked Accounts',
            'Muted Accounts'
        ];

        foreach ($defaultLists as $listName) {
            ListType::firstOrCreate([
                'name' => $listName,
                'is_default' => true
            ]);
        }
    }
}