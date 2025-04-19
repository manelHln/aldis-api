<?php

namespace App\Contracts;

use App\Models\Role;
use Illuminate\Http\Request;

interface RolesInterface{
    public function getAll(Request $request);
    public function createNew(array $data);
    public function getById(string $id);
    public function update(string $id, array $data);
    public function delete(string $id);
}
