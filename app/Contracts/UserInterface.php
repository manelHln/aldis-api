<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface UserInterface
{
    //
    public function createNew(array $data);
    public function delete(string $id);
    public function update(string $id, array $data);
    public function getAll(Request $request);
    public function getById(string $id);
    public function getCurrentUser(Request $request);
}
