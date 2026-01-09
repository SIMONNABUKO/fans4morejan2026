<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AutomatedMessage;

class AutomatedMessagePolicy
{
    public function update(User $user, AutomatedMessage $automatedMessage)
    {
        return $user->id === $automatedMessage->user_id;
    }

    public function delete(User $user, AutomatedMessage $automatedMessage)
    {
        return $user->id === $automatedMessage->user_id;
    }
}