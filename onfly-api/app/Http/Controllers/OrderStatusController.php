<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrderStatusController extends Controller
{
    public function index()
    {
        return response()->json(OrderStatus::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:order_statuses,name',
        ]);

        $status = OrderStatus::create([
            'name' => $validated['name'],
            'is_custom' => true,
        ]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($status)
            ->withProperties(['attributes' => $status->toArray()])
            ->log('Created custom order status');

    return response()->json($status, Response::HTTP_CREATED);
    }

    public function destroy($id)
    {
        $status = OrderStatus::findOrFail($id);
        if (!$status->is_custom) {
            return response()->json(['error' => 'Default statuses cannot be deleted.'], Response::HTTP_FORBIDDEN);
        }

        $status->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($status)
            ->withProperties(['attributes' => $status->toArray()])
            ->log('Deleted custom order status');

    return response()->json(['message' => 'Status deleted.'], Response::HTTP_OK);
    }
}
