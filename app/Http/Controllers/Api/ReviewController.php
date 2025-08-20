<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Review;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/reviews",
     *     tags={"Reviews"},
     *     summary="Get submissions for review",
     *     description="Retrieve list of submissions that need review or are assigned to current user",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by review status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "approved", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="assigned_to",
     *         in="query",
     *         description="Filter by assigned reviewer ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reviews retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Submission"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Display a listing of submissions that need review.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Submission::with(['user', 'attachments', 'contentItems'])
                ->where('workflow_stage', 'review')
                ->orWhere('status', 'confirmed');
            
            // Apply filters
            if ($request->has('status')) {
                $query->where('review_status', $request->status);
            }
            
            if ($request->has('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }
            
            // Only show items assigned to current user (if not admin)
            /** @var User $user */
            $user = Auth::user();
            if (!$user->hasRole(['admin', 'superadmin', 'review'])) {
                $query->where('assigned_to', $this->currentUserId());
            }
            
            $submissions = $query->orderBy('created_at', 'desc')->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $submissions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified submission for review.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $submission = Submission::with(['user', 'attachments', 'contentItems', 'reviews'])
                ->where('id', $id)
                ->where(function($query) {
                    $query->where('workflow_stage', 'review')
                          ->orWhere('status', 'confirmed');
                })
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => $submission
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reviews/{id}",
     *     tags={"Reviews"},
     *     summary="Submit review decision",
     *     description="Submit approval or rejection for a submission review",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Submission ID to review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status", "reviewerId"},
     *             @OA\Property(property="status", type="string", enum={"approved", "rejected"}, example="approved"),
     *             @OA\Property(property="notes", type="string", example="Review notes and feedback"),
     *             @OA\Property(property="reviewerId", type="string", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Submission"),
     *             @OA\Property(property="message", type="string", example="Review submitted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to review this submission"),
     *     @OA\Response(response=404, description="Submission not found"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Submit a review (approve/reject).
     */
    public function submitReview(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:approved,rejected',
                'notes' => 'nullable|string',
                'reviewerId' => 'required|string'
            ]);
            
            $submission = Submission::findOrFail($id);
            
            // Check if user can review this submission
            /** @var User $user */
            $user = Auth::user();
            if ($submission->assigned_to !== $this->currentUserId() && !$user->hasRole(['admin', 'superadmin', 'review'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to review this submission'
                ], 403);
            }
            
            // Create review record
            $review = Review::create([
                'submission_id' => $submission->id,
                'reviewer_id' => $validated['reviewerId'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'reviewed_at' => now()
            ]);
            
            // Update submission status
            $submission->update([
                'review_status' => $validated['status'],
                'review_notes' => $validated['notes'],
                'reviewed_by' => $validated['reviewerId'],
                'reviewed_at' => now(),
                'workflow_stage' => $validated['status'] === 'approved' ? 'validation' : 'completed'
            ]);
            
            $submission->load(['user', 'attachments', 'contentItems', 'reviews']);
            
            return response()->json([
                'success' => true,
                'data' => $submission,
                'message' => 'Review submitted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a reviewer to a submission.
     */
    public function assignReview(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'assigneeId' => 'required|string|exists:users,id'
            ]);
            
            $submission = Submission::findOrFail($id);
            
            // Check if user can assign reviews (admin/superadmin only)
            /** @var User $user */
            $user = Auth::user();
            if (!$user->hasRole(['admin', 'superadmin', 'review'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to assign reviews'
                ], 403);
            }
            
            $submission->update([
                'assigned_to' => $validated['assigneeId'],
                'assigned_at' => now(),
                'workflow_stage' => 'review'
            ]);
            
            $submission->load(['user', 'attachments', 'contentItems']);
            
            return response()->json([
                'success' => true,
                'data' => $submission,
                'message' => 'Review assigned successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign review',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}