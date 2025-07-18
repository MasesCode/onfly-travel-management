<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    private const ACCESS_DENIED_MESSAGE = 'Acesso negado';

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => self::ACCESS_DENIED_MESSAGE], 403);
        }

        $query = Activity::with(['subject', 'causer'])
            ->orderBy('created_at', 'desc');

        if ($request->has('log_name') && $request->log_name) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->has('causer_id') && $request->causer_id) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(15);

        return response()->json($logs);
    }

    public function show(Request $request, Activity $activity): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => self::ACCESS_DENIED_MESSAGE], 403);
        }

        $activity->load(['subject', 'causer']);

        return response()->json([
            'id' => $activity->id,
            'log_name' => $activity->log_name,
            'description' => $activity->description,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'causer_type' => $activity->causer_type,
            'causer_id' => $activity->causer_id,
            'properties' => $activity->properties,
            'created_at' => $activity->created_at,
            'updated_at' => $activity->updated_at,
            'subject' => $activity->subject,
            'causer' => $activity->causer ? [
                'id' => $activity->causer->id,
                'name' => $activity->causer->name,
                'email' => $activity->causer->email,
            ] : null,
        ]);
    }

    public function getLogNames(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => self::ACCESS_DENIED_MESSAGE], 403);
        }

        $logNames = Activity::distinct()
            ->pluck('log_name')
            ->filter()
            ->values();

        return response()->json($logNames);
    }
}
