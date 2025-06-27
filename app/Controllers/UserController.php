<?php

namespace App\Controllers;

use App\Core\Route;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UserController extends Controller
{
    #[Route('POST', '/users/login')]
    public function login(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $password = $request->input('password');

        if (!$username || !$password) {
            throw new BadRequestException('username and password are required');
        }

        $user = User::where('username', $username)->first();

        if (!$user || !password_verify($password, $user->password_hash)) {
            throw new BadRequestException('Invalid credentials');
        }

        return $this->success('Acesso liberado', ['user' => ['id' => $user->id, 'username' => $user->username, 'role' => $user->role, 'token' => '']]);
    }
}