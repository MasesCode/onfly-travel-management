<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\DestroyUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
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

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        $users->getCollection()->transform(function ($user) {
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

    public function store(StoreUserRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validated();

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

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $user = User::findOrFail($id);

        $validated = $request->validated();

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

    public function destroy(DestroyUserRequest $request, int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

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
