<?php

namespace App\Http\Controllers;

use App\Contracts\UserInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 */
class UserController extends Controller
{
    use AuthorizesRequests;
    public function __construct(protected UserInterface $userService)
    {
        // Constructor injection of UserInterface
    }
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Get all users",
     *     description="Retrieve a list of all users.",
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', $request->user());

        $data = $this->userService->getAll($request);

        return response()->json([
            "message" => "List of resources",
            "data" => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Add a new user to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $this->authorize('create', $request->user());
        $request->validate([
            'fullname' => 'required|string|min:5|max:255',
            'password' => ['required', Password::defaults()],
            'phone' => 'required|string|min:10|max:15|unique:users',
            'role_id' => 'required|uuid|exists:roles,id',
            'email' => 'required|email|unique:users',
        ]);
        $user = $this->userService->createNew($request->all());

        return response()->json([
            'message' => 'User created successfully.',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Get a user by ID",
     *     description="Retrieve details of a specific user by their ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show(Request $request, string $id)
    {
        $this->authorize('view', $request->user());
        $user = $this->userService->getById($id);

        return response()->json([
            "message" => "User retrieved successfully.",
            "data" => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Update a user",
     *     description="Update details of an existing user.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="email", type="string", example="updated@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('update', $request->user());
        $request->validate([
            'fullname' => 'sometimes|string|min:5|max:255',
            'email' => ['sometimes', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['sometimes', Rule::unique('users', 'phone')->ignore($id)],
            'role_id' => 'sometimes|uuid|exists:roles,id',
        ]);
        $user = $this->userService->update($id, $request->only("fullname", "email", "phone", "role_id"));

        return response()->json([
            "message" => "User updated successfully.",
            "data" => $user
        ], 200);
    }

    /**
     * Get the current authenticated user.
     */
    public function getCurrentUser(Request $request)
    {
        $this->authorize('view', $request->user());
        $user = $this->userService->getCurrentUser($request);

        return response()->json([
            "message" => "User retrieved successfully.",
            "data" => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     description="Remove a user from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorize('delete', $request->user());
        $user = $this->userService->delete($id);

        return response()->json([
            "message" => "User deleted successfully.",
            "data" => $user
        ], 200);
    }
}
