<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubmissionController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/submissions",
     *     tags={"Submissions"},
     *     summary="Get list of submissions",
     *     description="Retrieve paginated list of submissions with optional filters",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by submission status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "submitted", "confirmed", "review", "validation", "completed", "rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="workflow_stage",
     *         in="query",
     *         description="Filter by workflow stage",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft", "review", "validation", "completed"})
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Submissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Submission")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Display a listing of submissions.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Submission::with(['user', 'attachments', 'contentItems']);
            
            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->has('workflow_stage')) {
                $query->where('workflow_stage', $request->workflow_stage);
            }
            
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $submissions = $query->orderBy('created_at', 'desc')->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $submissions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch submissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/submissions",
     *     tags={"Submissions"},
     *     summary="Create new submission",
     *     description="Create a new submission with content items and attachments",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Sample Submission"),
     *             @OA\Property(property="description", type="string", example="This is a sample submission description"),
     *             @OA\Property(property="content_items", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="attachments", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="status", type="string", enum={"draft", "submitted", "confirmed"}, example="draft"),
     *             @OA\Property(property="workflow_stage", type="string", enum={"form", "review", "validation", "completed"}, example="form")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Submission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Submission"),
     *             @OA\Property(property="message", type="string", example="Submission created successfully")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Store a newly created submission.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'content_items' => 'nullable|array',
                'attachments' => 'nullable|array',
                'status' => 'nullable|string|in:draft,submitted,confirmed',
                'workflow_stage' => 'nullable|string|in:form,review,validation,completed'
            ]);
            
            $submission = Submission::create([
                ...$validated,
                'user_id' => $this->currentUserId(),
                'status' => $validated['status'] ?? 'draft',
                'workflow_stage' => $validated['workflow_stage'] ?? 'form'
            ]);
            
            // Handle content items if provided
            if (isset($validated['content_items'])) {
                foreach ($validated['content_items'] as $item) {
                    $submission->contentItems()->create($item);
                }
            }
            
            // Handle attachments if provided
            if (isset($validated['attachments'])) {
                foreach ($validated['attachments'] as $attachment) {
                    $submission->attachments()->create($attachment);
                }
            }
            
            $submission->load(['user', 'attachments', 'contentItems']);
            
            return response()->json([
                'success' => true,
                'data' => $submission,
                'message' => 'Submission created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified submission.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $submission = Submission::with(['user', 'attachments', 'contentItems', 'reviews', 'validations'])
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $submission
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Submission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified submission.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $submission = Submission::findOrFail($id);
            
            // Check if user can edit this submission
            if ($submission->user_id !== $this->currentUserId() && !$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to edit this submission'
                ], 403);
            }
            
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'content_items' => 'nullable|array',
                'attachments' => 'nullable|array',
                'status' => 'nullable|string|in:draft,submitted,confirmed',
                'workflow_stage' => 'nullable|string|in:form,review,validation,completed'
            ]);
            
            $submission->update($validated);
            
            // Update content items if provided
            if (isset($validated['content_items'])) {
                $submission->contentItems()->delete();
                foreach ($validated['content_items'] as $item) {
                    $submission->contentItems()->create($item);
                }
            }
            
            $submission->load(['user', 'attachments', 'contentItems']);
            
            return response()->json([
                'success' => true,
                'data' => $submission,
                'message' => 'Submission updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified submission.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $submission = Submission::findOrFail($id);
            
            // Check if user can delete this submission
            if ($submission->user_id !== $this->currentUserId() && !$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this submission'
                ], 403);
            }
            
            // Delete related records
            $submission->contentItems()->delete();
            $submission->attachments()->delete();
            $submission->reviews()->delete();
            $submission->validations()->delete();
            
            $submission->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Submission deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete submission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}