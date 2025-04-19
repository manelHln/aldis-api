<?php

namespace App\Http\Controllers;

use App\Contracts\UserLocationInterface;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="User Locations",
 *     description="User location management endpoints"
 * )
 */
class UserLocationController extends Controller{
    public function __construct(protected UserLocationInterface $userLocationService){}

    /**
     * @OA\Get(
     *     path="/user-locations",
     *     tags={"User Locations"},
     *     summary="Get all user locations",
     *     description="Retrieve a list of all user locations.",
     *     @OA\Response(
     *         response=200,
     *         description="List of user locations",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request){
        $data = $this->userLocationService->getAllUserLocations($request);

        return response()->json([
            "message" => "User locations retrieved successfully",
            "data" => $data,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/user-locations",
     *     tags={"User Locations"},
     *     summary="Create a new user location",
     *     description="Add a new user location to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address"},
     *             @OA\Property(property="address", type="string", example="123 Main St, City, Country")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User location created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User location created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request){
        $data = $request->validate([
            'latitude' => 'required|decimal:1,8',
            'longitude' => 'required|decimal:1,8',
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);
        $data['user_id'] = $request->user()->id;
        $result = $this->userLocationService->createNew( $data);

        return response()->json([
            "message" => "User location created successfully",
            "data" => $result,
        ], 201)->withHeaders([
            'Location' => route('user_location.show', ['id' => $result->id]),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user-locations/{id}",
     *     tags={"User Locations"},
     *     summary="Get a user location by ID",
     *     description="Retrieve details of a specific user location by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User location ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User location details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User location not found"
     *     )
     * )
     */
    public function show(Request $request, $id){
        $data = $this->userLocationService->getUserLocationById($id);

        return response()->json([
            "message" => "User location retrieved successfully",
            "data" => $data,
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/user-locations/{id}",
     *     tags={"User Locations"},
     *     summary="Update a user location",
     *     description="Update details of an existing user location.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User location ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="address", type="string", example="Updated Address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User location updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User location updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User location not found"
     *     )
     * )
     */
    public function update(Request $request, $id){
        $data = $request->validate([
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'title' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
        ]);

        $result = $this->userLocationService->updateUserLocation($id,  $data);

        return response()->json([
            "message" => "User location updated successfully",
            "data" => $result,
        ], 200)->withHeaders([
            'Location' => route('user_location.show', ['id' => $result->id]),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/user-locations/{id}",
     *     tags={"User Locations"},
     *     summary="Delete a user location",
     *     description="Remove a user location from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User location ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User location deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User location deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User location not found"
     *     )
     * )
     */
    public function destroy(Request $request, $id){
        $this->userLocationService->deleteUserLocation($id);

        return response()->json([
            "message" => "User location deleted successfully",
            "data" => null,
        ], 200);
    }
}
