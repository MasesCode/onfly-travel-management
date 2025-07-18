<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->is_admin) {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem acessar.'], Response::HTTP_FORBIDDEN);
        }

        $query = User::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        $users = $query->orderBy('created_at', 'desc')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->is_admin) {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem criar usuários.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_admin' => 'boolean',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $validated['is_admin'] ?? false,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($newUser)
            ->withProperties(['attributes' => $newUser->toArray()])
            ->log('Created user');

        return response()->json($newUser, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->is_admin) {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem editar usuários.'], Response::HTTP_FORBIDDEN);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
            'is_admin' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        activity()
            ->causedBy($currentUser)
            ->performedOn($user)
            ->withProperties(['attributes' => $user->toArray()])
            ->log('Updated user');

        return response()->json($user, Response::HTTP_OK);
    }

    public function destroy($id): JsonResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->is_admin) {
            return response()->json(['error' => 'Acesso negado. Apenas administradores podem excluir usuários.'], Response::HTTP_FORBIDDEN);
        }

        $user = User::findOrFail($id);

        if ($user->id === $currentUser->id) {
            return response()->json(['error' => 'Você não pode excluir sua própria conta.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        activity()
            ->causedBy($currentUser)
            ->performedOn($user)
            ->withProperties(['attributes' => $user->toArray()])
            ->log('Deleted user');

        $user->delete();

        return response()->json(['message' => 'Usuário excluído com sucesso.'], Response::HTTP_OK);
    }
}
