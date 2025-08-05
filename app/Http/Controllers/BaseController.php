<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
    //
}
