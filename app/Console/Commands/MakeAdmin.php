<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'admin:make {email : El email del usuario a promover}';
    protected $description = 'Promueve a un usuario existente a administrador';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user  = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No se encontró ningún usuario con el email: {$email}");
            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->warn("El usuario \"{$user->name}\" ya es administrador.");
            return self::SUCCESS;
        }

        $user->update(['is_admin' => true]);
        $this->info("✓ El usuario \"{$user->name}\" ({$email}) ahora es administrador.");

        return self::SUCCESS;
    }
}
