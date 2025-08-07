<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"User Management"},
     *     summary="Get list of users",
     *     description="Retrieve paginated list of users (Admin/SuperAdmin only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by user role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"admin", "user", "reviewer", "validator", "superadmin", "form", "review", "validasi", "rekap"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name, email, or username",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized - Admin access required"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Check if user has permission to view users
            if (!$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view users'
                ], 403);
            }
            
            $query = User::query();
            
            // Apply filters
            if ($request->has('role')) {
                $query->where('role', $request->role);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }
            
            $users = $query->orderBy('created_at', 'desc')->paginate(10);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"User Management"},
     *     summary="Create new user",
     *     description="Create a new user account (Admin/SuperAdmin only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "username", "password", "role"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="username", type="string", maxLength=255, example="johndoe"),
     *             @OA\Property(property="password", type="string", minLength=8, example="password123"),
     *             @OA\Property(property="role", type="string", enum={"admin", "user", "reviewer", "validator", "superadmin", "form", "review", "validasi", "rekap"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string", example="User created successfully")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized - Admin access required"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     * 
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check if user has permission to create users
            if (!$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create users'
                ], 403);
            }
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'username' => 'required|string|max:255|unique:users,username',
                'password' => 'required|string|min:8',
                'role' => 'required|string|in:admin,user,reviewer,validator,superadmin,form,review,validasi,rekap'
            ]);
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'email_verified_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User created successfully'
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): JsonResponse
    {
        try {
            // Check if user has permission or is viewing own profile
            if (!$this->currentUser()->hasRole(['admin', 'superadmin']) && $this->currentUserId() !== (int)$id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this user'
                ], 403);
            }
            
            $user = User::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            // Check if user has permission or is updating own profile
            if (!$this->currentUser()->hasRole(['admin', 'superadmin']) && $this->currentUserId() !== (int)$id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this user'
                ], 403);
            }
            
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
                'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
                'password' => 'sometimes|string|min:8',
                'role' => 'sometimes|string|in:admin,user,reviewer,validator,superadmin,form,review,validasi,rekap'
            ]);
            
            // Only admin/superadmin can change roles
            if (isset($validated['role']) && !$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                unset($validated['role']);
            }
            
            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
            
            $user->update($validated);
            
            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            // Check if user has permission to delete users
            if (!$this->currentUser()->hasRole(['admin', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete users'
                ], 403);
            }
            
            $user = User::findOrFail($id);
            
            // Prevent self-deletion
            if ($this->currentUserId() === (int)$id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account'
                ], 422);
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}