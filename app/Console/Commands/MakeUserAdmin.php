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

        if (!$email && !$this->input->isInteractive()) {
            $this->info('Admin bootstrap skipped: ADMIN_EMAIL is not configured.');

            return self::SUCCESS;
        }

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

        $attributes = ['is_admin' => true];
        $adminPassword = (string) env('ADMIN_PASSWORD', '');

        if ($adminPassword !== '') {
            if (strlen($adminPassword) < 8) {
                $this->error('ADMIN_PASSWORD must be at least 8 characters.');

                return self::FAILURE;
            }

            $attributes['password'] = $adminPassword;
        }

        $user->forceFill($attributes)->save();
        $this->info('Admin account is ready. Sign in at /admin.');

        return self::SUCCESS;
    }
}
