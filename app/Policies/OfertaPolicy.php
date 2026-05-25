<?php

namespace App\Policies;

use App\Models\Oferta;
use App\Models\User;

class OfertaPolicy
{
    public function update(User $user, Oferta $oferta): bool
    {
        return $user->id === $oferta->user_id;
    }

    public function delete(User $user, Oferta $oferta): bool
    {
        return $user->isAdmin() || $user->id === $oferta->user_id;
    }
}
