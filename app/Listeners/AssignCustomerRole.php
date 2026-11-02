<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AssignCustomerRole
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if ($event->user instanceof User) {
            $event->user->assignRole('customer');
        }
    }
}
