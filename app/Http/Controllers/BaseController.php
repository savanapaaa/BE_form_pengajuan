<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="BE Form Pengajuan API",
 *     version="1.0.0",
 *     description="API Documentation for BE Form Pengajuan",
 *     @OA\Contact(
 *         email="admin@budiutamamandiri.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="https://be-savana.budiutamamandiri.com",
 *     description="Production API Server"
 * )
 * 
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Local Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class BaseController extends Controller
{
    /**
     * Get current authenticated user with proper type
     */
    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user;
    }
    
    /**
     * Get current authenticated user ID
     */
    protected function currentUserId(): int
    {
        return Auth::id();
    }
}
