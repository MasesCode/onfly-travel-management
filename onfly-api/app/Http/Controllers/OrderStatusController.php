<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStatus\StoreOrderStatusRequest;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrderStatusController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Acesso negado.'], Response::HTTP_FORBIDDEN);
        }

        return response()->json(OrderStatus::all());
    }

    public function store(StoreOrderStatusRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Acesso negado.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        $status = OrderStatus::create([
            'name' => $validated['name'],
            'is_custom' => true,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($status)
            ->withProperties(['attributes' => $status->toArray()])
            ->log('Created custom order status');

        return response()->json($status, Response::HTTP_CREATED);
    }

    public function destroy(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Acesso negado.'], Response::HTTP_FORBIDDEN);
        }

        $status = OrderStatus::findOrFail($id);
        if (!$status->is_custom) {
            return response()->json(['error' => 'Default statuses cannot be deleted.'], Response::HTTP_FORBIDDEN);
        }

        $status->delete();

        activity()
            ->causedBy($user)
            ->performedOn($status)
            ->withProperties(['attributes' => $status->toArray()])
            ->log('Deleted custom order status');

        return response()->json(['message' => 'Status deleted.'], Response::HTTP_OK);
    }
}
