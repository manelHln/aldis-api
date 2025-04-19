<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_picture_url' => $this->profile_picture_url,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->map(fn ($permission) => $permission->name)
        ];
    }
}
