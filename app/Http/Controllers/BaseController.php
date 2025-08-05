<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="BE Form Pengajuan API",
 *     version="1.0.0",
 *     description="API Documentation for BE Form Pengajuan",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
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
    //
}
