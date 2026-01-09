<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ListService;

class UserObserver
{
    protected $listService;

    public function __construct(ListService $listService)
    {
        $this->listService = $listService;
    }

    public function created(User $user): void
    {
        $this->listService->createDefaultLists($user);
    }
}