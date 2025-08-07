<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'is_system' => (bool) $this->is_system,
            'is_active' => (bool) $this->is_active,
            
            // Permissions
            'permissions' => $this->whenLoaded('permissions', function () {
                return PermissionResource::collection($this->permissions);
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
