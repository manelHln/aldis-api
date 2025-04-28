<?php

namespace App\Services;

use App\Contracts\RolesInterface;
use App\Models\User;
use App\Contracts\UserInterface;
use Illuminate\Http\Request;
use App\Helpers\PaginationHelper;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class UserService implements UserInterface{
    public function __construct(protected RolesInterface $rolesService){}

    public function getAll(Request $request)
    {
        $query = User::query();

        if($request->has('size')){
            $size = $request->input('size');
            $cursor = $request->query("cursor");
            $result = $query->cursorPaginate($size, ['*'], 'cursor', $cursor);
            return PaginationHelper::cursorPaginated($result, UserResource::class);
        }

        return UserResource::collection($query->get());
    }

    public function createNew(array $data)
    {
        $user = new User();
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->fullname = $data['fullname'];
        $user->password = Hash::make($data['password']);

        $role = $this->rolesService->getById($data['role_id']);
        $user->assignRole($role->name);

        $user->save();
        return new UserResource($user);
    }

    public function getById(string $id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }

    public function getCurrentUser(Request $request)
    {
        $user = $request->user();
        return new UserResource($user);
    }

    public function update(string $id, array $data)
    {
        $user = User::findOrFail($id);
        $user->email = $data['email'] ?? $user->email;
        $user->phone = $data['phone'] ?? $user->phone;
        $user->fullname = $data['fullname'] ?? $user->fullname;

        if(isset($data['role_id'])){
            $role = $this->rolesService->getById($data['role_id']);
            $user->syncRoles($role->name);
        }
        $user->save();

        return new UserResource($user);
    }

    public function updatePassword(string $id, array $data)
    {
        $user = User::findOrFail($id);
        $user->password = Hash::make($data['password']);
        $user->save();

        return new UserResource($user);
    }

    public function delete(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return new UserResource($user);
    }
}
