<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a list of notifications.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     summary="Retrieve notifications for the authenticated user",
     *     description="Fetch a list of all notifications for the authenticated user, sorted by the latest.",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=101),
     *                     @OA\Property(property="message", type="string", example="Your account was credited."),
     *                     @OA\Property(property="is_read", type="boolean", example=false),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    /**
     * @OA\Patch(
     *     path="/api/v1/notifications/{id}/mark-as-read",
     *     summary="Mark a notification as read",
     *     description="Mark a specific notification as read for the authenticated user. The user must be authorized to perform this action.",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the account to retrieve",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Notification marked as read.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Notification not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to mark notification as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="failure"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        // Check authorization
        $this->authorize('update', $notification);

        $notification->update(['is_read' => true]);

        return response()->json(['status' => 'success', 'message' => 'Notification marked as read.']);
    }
}
