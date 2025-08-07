<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Validation;
use App\Models\Submission;
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
     *     description="Retrieve list of submissions that need validation (approved by reviewers)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by validation status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "validated", "published", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="assigned_to",
     *         in="query",
     *         description="Filter by assigned validator ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validations retrieved successfully",
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
     * Display a listing of submissions that need validation.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Submission::with(['user', 'attachments', 'contentItems'])
                ->where('workflow_stage', 'validation')
                ->where('review_status', 'approved');
            
            // Apply filters
            if ($request->has('status')) {
                $query->where('validation_status', $request->status);
            }
            
            if ($request->has('assigned_to')) {
                $query->where('validation_assigned_to', $request->assigned_to);
            }
            
            // Only show items assigned to current user (if not admin)
                $query->where('validation_assigned_to', $this->currentUserId());
            if (!$this->currentUser()->hasRole(['admin', 'superadmin'])) {
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
     * Display the specified submission for validation.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $submission = Submission::with(['user', 'attachments', 'contentItems', 'reviews', 'validations'])
                ->where('id', $id)
                ->where('workflow_stage', 'validation')
                ->where('review_status', 'approved')
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => $submission
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/validations/{id}",
     *     tags={"Validations"},
     *     summary="Submit validation decision",
     *     description="Submit validation, publish, or rejection for an approved submission",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Submission ID to validate",
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
     *             @OA\Property(property="data", ref="#/components/schemas/Submission"),
     *             @OA\Property(property="message", type="string", example="Validation submitted successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized to validate this submission"),
     *     @OA\Response(response=404, description="Submission not found"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Submit a validation (validate/publish/reject).
     */
    public function submitValidation(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:validated,published,rejected',
                'notes' => 'nullable|string',
                'validatorId' => 'required|string',
                'publishDate' => 'nullable|date',
                'publishedContent' => 'nullable|array'
            ]);
            
            $submission = Submission::findOrFail($id);
            
            // Check if user can validate this submission
            if ($submission->validation_assigned_to !== $this->currentUserId() && !$this->currentUser()->hasRole(['admin', 'superadmin', 'validator'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to validate this submission'
                ], 403);
            }
            
            // Create validation record
            $validation = Validation::create([
                'submission_id' => $submission->id,
                'validator_id' => $validated['validatorId'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'publish_date' => $validated['publishDate'] ?? null,
                'published_content' => $validated['publishedContent'] ?? null,
                'validated_at' => now()
            ]);
            
            // Update submission status
            $submission->update([
                'validation_status' => $validated['status'],
                'validation_notes' => $validated['notes'],
                'validated_by' => $validated['validatorId'],
                'validated_at' => now(),
                'publish_date' => $validated['publishDate'] ?? null,
                'published_content' => $validated['publishedContent'] ?? null,
                'workflow_stage' => $validated['status'] === 'published' ? 'completed' : 'validation'
            ]);
            
            $submission->load(['user', 'attachments', 'contentItems', 'reviews', 'validations']);
            
            return response()->json([
                'success' => true,
                'data' => $submission,
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
     * Assign a validator to a submission.
     */
    public function assignValidation(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'assigneeId' => 'required|string|exists:users,id'
            ]);
            
            $submission = Submission::findOrFail($id);
            
            // Check if user can assign validations (admin/superadmin only)
            if (!$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to assign validations'
                ], 403);
            }
            
            $submission->update([
                'validation_assigned_to' => $validated['assigneeId'],
                'validation_assigned_at' => now()
            ]);
            
            $submission->load(['user', 'attachments', 'contentItems']);
            
            return response()->json([
                'success' => true,
                'data' => $submission,
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
}