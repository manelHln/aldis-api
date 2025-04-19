<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     title="Aldis API",
 *     version="1.0.0",
 *     description="API documentation for Aldis application"
 * )
 * @OA\Response(
 *         response="ServerError",
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Internal server error"),
 *             @OA\Property(property="code", type="integer", example=500)
 *         )
 *     ),
 *     @OA\Response(
 *         response="Unauthorized",
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 * )
 */
abstract class Controller extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests;
}
