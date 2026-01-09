<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserLocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-location {username} {--country=KE} {--region=Nairobi County} {--city=Nairobi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user location for testing geoblocking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $country = $this->option('country');
        $region = $this->option('region');
        $city = $this->option('city');

        $user = User::where('username', $username)->first();

        if (!$user) {
            $this->error("User with username '{$username}' not found.");
            return 1;
        }

        $user->update([
            'country_code' => $country,
            'country_name' => 'Kenya',
            'region_name' => $region,
            'city_name' => $city,
            'location_updated_at' => now(),
        ]);

        $this->info("User '{$username}' location updated successfully!");
        $this->info("Country: {$country}");
        $this->info("Region: {$region}");
        $this->info("City: {$city}");

        return 0;
    }
}
