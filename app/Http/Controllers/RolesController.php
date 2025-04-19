<?php

namespace App\Http\Controllers;

use App\Models\UserRole;
use App\Contracts\RolesInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="Role management endpoints"
 * )
 */
class RolesController extends Controller
{
    public function __construct(protected RolesInterface $rolesService) {}
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/roles",
     *     tags={"Roles"},
     *     summary="Get all roles",
     *     description="Retrieve a list of all roles.",
     *     @OA\Response(
     *         response=200,
     *         description="List of roles",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        //
        $data = $this->rolesService->getAll($request);

        return response()->json([
            "message" => 'List of resources',
            "data" => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/roles",
     *     tags={"Roles"},
     *     summary="Create a new role",
     *     description="Add a new role to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Admin"),
     *            @OA\Property(property="description", type="string", example="Administrator role"),
     *            @OA\Property(property="permissions", type="array", @OA\Items(type="string", example="01964471-756f-70c1-8361-a7bbaa82e609"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
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
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'description' => 'nullable|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'uuid|exists:permissions,id',
        ]);

        $role = $this->rolesService->createNew($request->only("name", "permissions"));

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => $role
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/roles/{id}",
     *     tags={"Roles"},
     *     summary="Get a role by ID",
     *     description="Retrieve details of a specific role by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        //
        $role = $this->rolesService->getById($id);
        return response()->json([
            "message" => 'Role retrieved successfully.',
            "data" => $role
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/roles/{id}",
     *     tags={"Roles"},
     *     summary="Update a role",
     *     description="Update details of an existing role.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Role Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($id)],
            'description' => 'sometimes|nullable|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,id',
        ]);

        $role = $this->rolesService->update($id, $request->only("name", "permissions", "description"));

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => $role
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/roles/{id}",
     *     tags={"Roles"},
     *     summary="Delete a role",
     *     description="Remove a role from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $this->rolesService->delete($id);

        return response()->json([
            'message' => 'Role deleted successfully.',
            'data' => null
        ], 200);
    }
}
