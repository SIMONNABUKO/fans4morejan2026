<?php

namespace App\Providers;

use App\Contracts\BlockedLocationRepositoryInterface;
use App\Contracts\CreatorApplicationRepositoryInterface;
use App\Contracts\LoginRepositoryInterface;
use App\Contracts\MessageRepositoryInterface;
use App\Contracts\PostRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\BlockedLocationRepository;
use App\Repositories\CreatorApplicationRepository;
use App\Repositories\LoginRepository;
use App\Repositories\MessageRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Services\CoverPhotoService;
use Illuminate\Support\ServiceProvider;
use App\Notifications\Channels\CustomDatabaseChannel;
use Illuminate\Notifications\Channels\DatabaseChannel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(BlockedLocationRepositoryInterface::class, BlockedLocationRepository::class);
        $this->app->bind(LoginRepositoryInterface::class, LoginRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(CreatorApplicationRepositoryInterface::class, CreatorApplicationRepository::class);
        $this->app->bind(DatabaseChannel::class, function ($app) {
            return new CustomDatabaseChannel();
        });


    
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
