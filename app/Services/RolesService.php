<?php

namespace App\Services;

use App\Exceptions\AldisModelNotFoundException;
use App\Models\Role;
use App\Contracts\RolesInterface;
use App\Helpers\PaginationHelper;
use Illuminate\Http\Request;

class RolesService implements RolesInterface
{

    public function getAll(Request $request)
    {
        $query = Role::query();

        if ($request->has('size')) {
            $size = $request->input('size');
            $cursor = $request->query("cursor");
            $result = $query->cursorPaginate($size, ['*'], 'cursor', $cursor);
            return PaginationHelper::cursorPaginated($result);
        }

        return $query->get();
    }

    public function createNew(array $data)
    {
        $role = new Role();
        $role->name = $data['name'];
        $role->description = $data['description'] ?? null;
        $role->guard_name = 'web';
        $role->save();

        if (isset($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }
        return $role;
    }

    public function getById(string $id)
    {
        $role = Role::with(['permissions:id,name,description'])->find($id);
        if (!$role){
            throw new AldisModelNotFoundException("Role with id $id not found");
        }
        return $role;
    }

    public function update(string $id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role;
    }
    public function delete(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return true;
    }
}
