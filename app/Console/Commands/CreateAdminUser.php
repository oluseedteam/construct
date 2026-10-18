<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create {email? : Login email} {--name= : Display name} {--role=admin : admin or editor}')]
#[Description('Create (or reset the password of) an admin dashboard user')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email') ?: text('Email address', required: true);
        $name = $this->option('name') ?: text('Name', default: 'Administrator', required: true);
        $role = $this->option('role');
        $plain = password('Password (min 12 characters)', required: true);

        $validator = Validator::make(
            ['email' => $email, 'role' => $role, 'password' => $plain],
            [
                'email' => ['required', 'email'],
                'role' => ['required', 'in:'.User::ROLE_ADMIN.','.User::ROLE_EDITOR],
                'password' => ['required', Password::min(12)->letters()->numbers()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $plain, 'role' => $role, 'is_active' => true],
        );

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated')." {$role} account for {$email}.");

        return self::SUCCESS;
    }
}
