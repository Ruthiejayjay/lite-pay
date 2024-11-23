<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Notification $notification)
    {
        // Allow only if the notification belongs to the authenticated user
        return $notification->user_id === $user->id;
    }
}
