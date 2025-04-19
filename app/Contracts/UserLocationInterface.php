<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface UserLocationInterface{
    public function createNew(array $data);
    public function getAllUserLocations(Request $request);
    public function getUserLocationById(string $id);
    public function updateUserLocation(string $id, array $data);
    public function deleteUserLocation(string $id);
}
