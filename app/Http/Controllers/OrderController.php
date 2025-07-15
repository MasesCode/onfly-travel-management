<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use App\Notifications\OrderStatusChangedNotification;

class OrderController extends Controller
{
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Apenas administradores podem alterar o status.'], Response::HTTP_FORBIDDEN);
        }

        if ($order->user_id === $user->id) {
            return response()->json(['error' => 'Você não pode alterar o status do seu próprio pedido.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'cancelled'])],
        ]);

        $newStatus = OrderStatus::where('name', $validated['status'])->firstOrFail();

        if ($validated['status'] === 'cancelled' && $order->status->name === 'approved') {
            return response()->json(['error' => 'Não é possível cancelar um pedido já aprovado.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->order_status_id = $newStatus->id;
        $order->save();

        if (in_array($validated['status'], ['approved', 'cancelled'])) {
            $order->user->notify(new OrderStatusChangedNotification($order));
        }

        activity()
            ->causedBy($user)
            ->performedOn($order)
            ->withProperties([
                'attributes' => $order->toArray(),
                'new_status' => $validated['status'],
            ])
            ->log('Updated order status');

    return response()->json($order, Response::HTTP_OK);
    }
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['status', 'user'])
            ->where('user_id', Auth::id());

        if ($request->has('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        if ($request->has('destination')) {
            $query->where('destination', $request->destination);
        }

        if ($request->has('from') && $request->has('to')) {
            $query->whereBetween('departure_date', [$request->from, $request->to]);
        }

        $orders = $query->get();

        return response()->json($orders);
    }

    public function show($id): JsonResponse
    {
        $order = Order::with(['status', 'user'])->where('user_id', Auth::id())->findOrFail($id);

        return response()->json($order);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'destination' => 'required|string',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
        ]);

        $userId = $validated['user_id'] ?? Auth::id();
        $user = User::findOrFail($userId);

        $status = OrderStatus::where('name', 'requested')->firstOrFail();

        $order = Order::create([
            'user_id' => $user->id,
            'order_status_id' => $status->id,
            'requester_name' => $user->name,
            'destination' => $validated['destination'],
            'departure_date' => $validated['departure_date'],
            'return_date' => $validated['return_date'],
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($order)
            ->withProperties([
                'attributes' => $order->toArray(),
                'created_for_user_id' => $user->id,
                'created_for_user_name' => $user->name,
            ])
            ->log('Created order');

        return response()->json($order, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'destination' => 'sometimes|string',
            'departure_date' => 'sometimes|date',
            'return_date' => 'sometimes|date|after_or_equal:departure_date',
        ]);

        $order->update($validated);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($order)
            ->withProperties(['attributes' => $order->toArray()])
            ->log('Updated order');

    return response()->json($order, Response::HTTP_OK);
    }

    public function destroy($id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $order->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($order)
            ->withProperties(['attributes' => $order->toArray()])
            ->log('Deleted order');

    return response()->json(['message' => 'Order deleted.'], Response::HTTP_OK);
    }
}
