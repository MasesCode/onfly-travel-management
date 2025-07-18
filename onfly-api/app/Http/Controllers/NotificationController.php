<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $query = $user->notifications()
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 10);
        $notifications = $query->paginate($perPage);

        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'info',
                'data' => $notification->data,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notificação marcada como lida']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Todas as notificações foram marcadas como lidas']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notificação removida']);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->notifications()->delete();

        return response()->json(['message' => 'Todas as notificações foram removidas']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $count = $user->unreadNotifications()->count();

        return response()->json(['unread_count' => $count]);
    }
}
