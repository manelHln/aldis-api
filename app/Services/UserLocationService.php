<?php

namespace App\Services;
use App\Models\UserLocation;
use App\Contracts\UserLocationInterface;
use App\Helpers\PaginationHelper;
use Illuminate\Http\Request;

class UserLocationService implements UserLocationInterface{
    public function createNew(array $data)
    {
        $location = new UserLocation();
        $location->title = $data['title'];
        $location->lat = $data['latitude'];
        $location->long = $data['longitude'];
        $location->address = $data['address'];
        $location->user_id = $data['user_id'];

        $location->save();

        return $location;
    }
    public function getAllUserLocations(Request $request)
    {
        $userLocationsQuery = UserLocation::query();
        $userLocationsQuery->where('user_id', $request->user()->id);
        $userLocationsQuery->orderBy('created_at', 'desc');
        if($request->has('size')){
            $size = $request->input('size');
            $cursor = $request->query("cursor");
            $result = $userLocationsQuery->cursorPaginate($size, ['*'], 'cursor', $cursor);
            return PaginationHelper::cursorPaginated($result);
        }

        return $userLocationsQuery->get();
    }
    public function getUserLocationById(string $id)
    {
        $user_location = UserLocation::findOrFail($id);
        return $user_location;
    }
    public function updateUserLocation(string $id, array $data)
    {
        $user_location = UserLocation::findOrFail($id);
        $user_location->title = $data['title'] ?? $user_location->title;
        $user_location->lat = $data['latitude'] ?? $user_location->lat;
        $user_location->long = $data['longitude'] ?? $user_location->long;
        $user_location->address = $data['address'] ?? $user_location->address;

        $user_location->save();

        return $user_location;
    }
    public function deleteUserLocation(string $id)
    {
        $user_location = UserLocation::findOrFail($id);
        $user_location->delete();
        return true;
    }
}
