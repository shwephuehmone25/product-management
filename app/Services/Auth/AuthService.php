<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    /**
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'customer',
        ];

        $user = $this->users->create($payload);
        $token = $user->createToken('api')->plainTextToken;

        return compact('user', 'token');
    }

    /**
     * @return array{user: User, token: string}
     */
    public function login(array $credentials): array
    {
        $user = $this->users->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new UnauthorizedHttpException('', 'Invalid credentials');
        }

        $token = $user->createToken('api')->plainTextToken;

        return compact('user', 'token');
    }
}
