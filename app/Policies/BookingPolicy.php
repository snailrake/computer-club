<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $booking->user_id === $user->id;
    }

    public function update(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking) && $booking->starts_at->isFuture();
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking) && ($user->isAdmin() || $booking->starts_at->isFuture());
    }
}
