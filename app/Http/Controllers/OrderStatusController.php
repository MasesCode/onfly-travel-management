<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return response()->json($status, 201);
    }

    public function destroy($id)
    {
        $status = OrderStatus::findOrFail($id);
        if (!$status->is_custom) {
            return response()->json(['error' => 'Default statuses cannot be deleted.'], 403);
        }

        $status->delete();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($status)
            ->withProperties(['attributes' => $status->toArray()])
            ->log('Deleted custom order status');

        return response()->json(['message' => 'Status deleted.']);
    }
}
