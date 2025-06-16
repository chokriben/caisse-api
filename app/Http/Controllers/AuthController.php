<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Connexion avec code + gestion rôles
     */
  public function login(Request $request)
{
    $request->validate([
        'code' => 'required|string',
        'password' => 'required|string',
    ]);

    $user = User::where('code', $request->code)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Identifiants incorrects.'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    /**
     * Enregistrement utilisateur
     */
  public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:users,email',
        'code' => 'required|string|unique:users,code',
        'role' => 'required|in:admin,caisser',
        'password' => 'required|string|min:6|confirmed',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'code' => $request->code,
        'role' => $request->role,
        'password' => Hash::make($request->password),
    ]);

    return response()->json([
        'message' => 'Utilisateur créé avec succès',
        'user' => $user
    ], 201);
}
public function getUsers()
    {
        $users = User::all();

        return response()->json([
            'users' => $users,
        ]);
    }

}
