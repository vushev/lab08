<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class UserService
{
    public function create(string $name, string $email ): User
    {
        return User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make('password'),
        ]);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
