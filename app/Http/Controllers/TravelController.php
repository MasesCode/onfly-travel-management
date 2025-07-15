<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TravelController extends Controller
{
    public function store(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        $validated = $request->validate([
            'pickup_address' => 'required|string',
            'delivery_address' => 'required|string',
            'recipient_name' => 'required|string',
            'recipient_email' => 'required|email',
            'recipient_cpf' => ['required', 'string', 'regex:/^\d{11}$/'],
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
        ]);
        $travel = Travel::create(array_merge($validated, [
            'order_id' => $order->id,
            'is_private_send' => false
        ]));
        activity()
            ->causedBy(Auth::user())
            ->performedOn($travel)
            ->withProperties(['attributes' => $travel->toArray()])
            ->log('Created travel');
        return response()->json($travel, Response::HTTP_CREATED);
    }

    public function update(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        if ($order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        $travel = Travel::where('order_id', $order->id)->firstOrFail();
        $validated = $request->validate([
            'pickup_address' => 'sometimes|string',
            'delivery_address' => 'sometimes|string',
            'recipient_name' => 'sometimes|string',
            'recipient_email' => 'sometimes|email',
            'recipient_cpf' => ['sometimes', 'string', 'regex:/^\d{11}$/'],
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
        ]);
        $travel->update($validated);
        activity()
            ->causedBy(Auth::user())
            ->performedOn($travel)
            ->withProperties(['attributes' => $travel->toArray()])
            ->log('Updated travel');
        return response()->json($travel, Response::HTTP_OK);
    }
}
