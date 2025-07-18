<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Apenas administradores podem alterar o status de pedidos.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        $newStatus = OrderStatus::where('name', $validated['status'])->firstOrFail();
        $oldStatusId = $order->order_status_id;

        if ($validated['status'] === 'cancelled' && $order->status->name === 'approved') {
            return response()->json(['error' => 'Não é possível cancelar um pedido já aprovado.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($order->status->name === 'cancelled') {
            return response()->json(['error' => 'Não é possível alterar o status de um pedido cancelado.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->order_status_id = $newStatus->id;
        $order->save();

        $order->user->notify(new OrderStatusChanged($order, $oldStatusId, $newStatus->id));

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
        /** @var User $user */
        $user = Auth::user();

        $query = Order::with(['status', 'user']);

        if (!$user->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        if ($request->has('status')) {
            $query->whereHas('status', function ($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        if ($request->has('destination')) {
            $query->where('destination', 'like', '%' . $request->destination . '%');
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('departure_date', [$request->start_date, $request->end_date]);
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 10);
        $orders = $query->paginate($perPage);

        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'requester' => $order->user->name,
                'destination' => $order->destination,
                'start_date' => $order->departure_date,
                'end_date' => $order->return_date,
                'status' => $order->status->name,
                'notes' => $order->notes ?? '',
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        return response()->json($orders);
    }

    public function show(int $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $query = Order::with(['status', 'user']);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $order = $query->findOrFail($id);

        return response()->json($order);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

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

    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            return response()->json(['error' => 'Você só pode editar seus próprios pedidos.'], Response::HTTP_FORBIDDEN);
        }

        if (!$user->isAdmin() && $order->status->name === 'approved') {
            return response()->json(['error' => 'Não é possível editar um pedido já aprovado.'], Response::HTTP_FORBIDDEN);
        }

        if (!$user->isAdmin() && $order->status->name === 'cancelled') {
            return response()->json(['error' => 'Não é possível editar um pedido cancelado.'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        $order->update($validated);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($order)
            ->withProperties(['attributes' => $order->toArray()])
            ->log('Updated order');

        return response()->json($order, Response::HTTP_OK);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        /** @var User $user */
        $user = Auth::user();

        if ($order->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $order->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($order)
            ->withProperties(['attributes' => $order->toArray()])
            ->log('Deleted order');

        return response()->json(['message' => 'Pedido excluído com sucesso.'], Response::HTTP_OK);
    }
}
