<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'app:make-admin {email}';

    protected $description = 'Grant admin panel access to a user';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $name = trim((string) $this->ask('Name'));
            $password = (string) $this->secret('Password');
            $confirmation = (string) $this->secret('Confirm password');

            if ($name === '' || strlen($password) < 8 || $password !== $confirmation) {
                $this->error('Enter a name, a matching password, and at least 8 password characters.');

                return self::FAILURE;
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        }

        $user->forceFill(['is_admin' => true])->save();
        $this->info('Admin account is ready. Sign in at /admin.');

        return self::SUCCESS;
    }
}
