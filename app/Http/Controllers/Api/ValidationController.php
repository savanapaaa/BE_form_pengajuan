<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Validation;
use App\Models\Submission;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ValidationController extends BaseController
{
        /**
         * @OA\Get(
         *     path="/api/validations",
         *     tags={"Validations"},
         *     summary="Get submissions for validation",
         *     description="Retrieve list of submissions that need validation (workflow_stage = validation)",
         *     security={{"sanctum":{}}},
         *     @OA\Parameter(
         *         name="status",
         *         in="query",
         *         description="Filter by validation status (custom app logic, not always present)",
         *         required=false,
         *         @OA\Schema(type="string")
         *     ),
         *     @OA\Parameter(
         *         name="assigned_to",
         *         in="query",
         *         description="Filter by assigned validator user ID",
         *         required=false,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Submissions retrieved successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", type="object",
         *                 @OA\Property(property="current_page", type="integer", example=1),
         *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Submission")),
         *                 @OA\Property(property="total", type="integer", example=2)
         *             )
         *         )
         *     ),
         *     @OA\Response(response=401, description="Unauthenticated"),
         *     @OA\Response(response=500, description="Server Error")
         * )
         *
         * Display a listing of submissions that need validation.
         */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get submissions that are in validation stage
            $query = Submission::with(['user', 'reviews.reviewer', 'validations.validator', 'assignedValidator'])
                ->where('workflow_stage', 'validation');
            
            // Apply filters
            if ($request->has('status')) {
                $query->where('validation_status', $request->status);
            }
            
            if ($request->has('assigned_to')) {
                $query->where('assigned_validator', $request->assigned_to);
            }
            
            // Only show items assigned to current user (if not admin)
            $currentUser = $this->currentUser();
            if ($currentUser && !$currentUser->hasRole(['admin', 'superadmin'])) {
                $query->where('assigned_validator', $this->currentUserId());
            }
            
            $submissions = $query->orderBy('created_at', 'desc')->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $submissions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch validations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified content item for validation.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $contentItem = ContentItem::with(['submission.user', 'submission.reviews', 'reviewer', 'validationAssignee', 'validator'])
                ->whereHas('submission.reviews', function ($q) {
                    $q->where('status', 'approved');
                })
                ->where('id', $id)
                ->where('workflow_stage', 'validation')
                ->where('review_status', 'approved')
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => $contentItem
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Content item not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/validations/{id}",
     *     tags={"Validations"},
     *     summary="Submit validation decision",
     *     description="Submit validation, publish, or rejection for an approved content item",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Content Item ID to validate",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status", "validatorId"},
     *             @OA\Property(property="status", type="string", enum={"validated", "published", "rejected"}, example="validated"),
     *             @OA\Property(property="notes", type="string", example="Validation notes and feedback"),
     *             @OA\Property(property="validatorId", type="string", example="1"),
     *             @OA\Property(property="publishDate", type="string", format="date", example="2025-08-15"),
     *             @OA\Property(property="publishedContent", type="object", example={"platform": "website", "scheduled": true})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validation submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ContentItem"),
     *             @OA\Property(property="message", type="string", example="Validation submitted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to validate this content item"),
     *     @OA\Response(response=404, description="Content item not found"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Submit a validation (validate/publish/reject).
     */
    public function submitValidation(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:validated,published,rejected',
                'notes' => 'nullable|string',
                'validator_id' => 'required|string',
                'publishDate' => 'nullable|date',
                'publishedContent' => 'nullable|array'
            ]);
            
            $contentItem = ContentItem::where('submission_id', $id)->firstOrFail();
            // return response()->json($contentItem);

            // Check if content item is in valid state for validation
            if ($contentItem->workflow_stage !== 'review' || $contentItem->review_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Content item is not ready for validation'
                ], 422);
            }
            
            // Check if user can validate this content item
            if ($contentItem->validation_assigned_to !== $this->currentUserId() && !$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to validate this content item'
                ], 403);
            }

            $submission = Submission::where('id', $id)->update([
                'workflow_stage' => 'completed'
            ]);
            
            // Create validation record
            $validation = Validation::create([
                'submission_id' => $contentItem->submission_id,
                'validator_id' => $validated['validator_id'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'publish_date' => $validated['publishDate'] ?? null,
                'published_content' => $validated['publishedContent'] ?? null,
            ]);
            
            // Update content item status
            $contentItem->update([
                'validation_status' => $validated['status'],
                'validation_notes' => $validated['notes'],
                'validated_by' => $validated['validator_id'],
                'validated_at' => now(),
                'publish_date' => $validated['publishDate'] ?? null,
                'published_content' => $validated['publishedContent'] ?? null,
                'workflow_stage' => $validated['status'] === 'validated' ? 'completed' : 'validation'
            ]);
            
            $contentItem->load(['submission.user', 'reviewer', 'validationAssignee', 'validator']);
            
            return response()->json([
                'success' => true,
                'data' => $contentItem,
                'message' => 'Validation submitted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit validation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/validations/{id}/assign",
     *     tags={"Validations"},
     *     summary="Assign validator to content item",
     *     description="Assign a validator to a content item for validation",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Content Item ID to assign validator",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"assigneeId"},
     *             @OA\Property(property="assigneeId", type="string", example="1", description="ID of user to assign as validator")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validation assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ContentItem"),
     *             @OA\Property(property="message", type="string", example="Validation assigned successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to assign validations"),
     *     @OA\Response(response=404, description="Content item not found"),
     *     @OA\Response(response=422, description="Content item not ready for validation assignment"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Assign a validator to a content item.
     */
    public function assignValidation(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'assigneeId' => 'required|string|exists:users,id'
            ]);
            
            $contentItem = ContentItem::findOrFail($id);
            
            // Check if content item is ready for validation assignment
            if ($contentItem->workflow_stage !== 'validation' || $contentItem->review_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Content item is not ready for validation assignment'
                ], 422);
            }
            
            // Check if user can assign validations (admin/superadmin only)
            if (!$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to assign validations'
                ], 403);
            }
            
            $contentItem->update([
                'validation_assigned_to' => $validated['assigneeId'],
                'validation_assigned_at' => now()
            ]);
            
            $contentItem->load(['submission.user', 'reviewer', 'validationAssignee', 'validator']);
            
            return response()->json([
                'success' => true,
                'data' => $contentItem,
                'message' => 'Validation assigned successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign validation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/validations/validators",
     *     tags={"Validations"},
     *     summary="Get list of available validators",
     *     description="Retrieve list of users who can validate content",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Validators retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Get list of available validators.
     */
    public function getValidators(): JsonResponse
    {
        try {
            // Check if user can view validators (admin/superadmin only)
            if (!$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view validators'
                ], 403);
            }

            $validators = User::whereIn('role', ['validasi', 'admin', 'superadmin'])
                ->select('id', 'name', 'email', 'role')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $validators
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch validators',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/validations/reviewed-submissions",
     *     tags={"Validations"},
     *     summary="Get reviewed submissions ready for content creation",
     *     description="Retrieve list of submissions that have been reviewed and approved, ready for content item creation",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Reviewed submissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Submission"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Get reviewed submissions that are ready for content creation and validation.
     */
    public function getReviewedSubmissions(): JsonResponse
    {
        try {
            // Check if user can view submissions (admin/superadmin/validasi only)
            if (!$this->currentUser()->hasRole(['admin', 'superadmin', 'validasi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view reviewed submissions'
                ], 403);
            }

            $submissions = Submission::with(['user', 'reviews' => function($q) {
                    $q->where('status', 'approved')->with('reviewer');
                }])
                ->whereHas('reviews', function ($q) {
                    $q->where('status', 'approved');
                })
                ->where('workflow_stage', 'review')
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $submissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reviewed submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}