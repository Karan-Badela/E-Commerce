<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    protected $signature = 'app:make-admin {email?}';

    protected $description = 'Grant admin panel access to a user';

    public function handle(): int
    {
        $email = $this->argument('email') ?: env('ADMIN_EMAIL');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $name = trim((string) env('ADMIN_NAME', ''));
            $password = (string) env('ADMIN_PASSWORD', '');

            if ($name === '' && $this->input->isInteractive()) {
                $name = trim((string) $this->ask('Name'));
            }

            if ($password === '' && $this->input->isInteractive()) {
                $password = (string) $this->secret('Password');
                $confirmation = (string) $this->secret('Confirm password');

                if ($password !== $confirmation) {
                    $this->error('The passwords do not match.');

                    return self::FAILURE;
                }
            }

            if ($name === '' || strlen($password) < 8) {
                $this->error('Provide ADMIN_NAME and an ADMIN_PASSWORD of at least 8 characters.');

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
