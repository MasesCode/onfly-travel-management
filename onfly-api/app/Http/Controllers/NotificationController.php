<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->whereNull('deleted_at') // Filtrar apenas notificações não deletadas
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'info',
                    'title' => $notification->data['title'] ?? 'Notificação',
                    'message' => $notification->data['message'] ?? '',
                    'read' => !is_null($notification->read_at),
                    'deleted' => false, // Para compatibilidade com o frontend
                    'created_at' => $notification->created_at->toISOString(),
                    'order_id' => $notification->data['order_id'] ?? null,
                    'destination' => $notification->data['destination'] ?? null,
                ];
            });

        return response()->json([
            'data' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notificação não encontrada'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notificação marcada como lida']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Todas as notificações foram marcadas como lidas']);
    }

    /**
     * Delete notification (soft delete)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notificação não encontrada'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notificação removida']);
    }

    /**
     * Delete all notifications
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $user = Auth::user();

        $user->notifications()->delete();

        return response()->json(['message' => 'Todas as notificações foram removidas']);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();

        $count = $user->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }
}
