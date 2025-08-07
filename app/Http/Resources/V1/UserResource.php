<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'status' => $this->status,
            'is_root' => $this->is_root,
            'last_visit' => $this->last_visit,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include roles and permissions when needed
            'roles' => $this->whenLoaded('roles', function () {
                return RoleResource::collection($this->roles);
            }),
            
            'permissions' => $this->when($request->include_permissions, function () {
                return $this->getAllPermissions()->pluck('name');
            }),
            
            // Include branch shops when needed
            'branch_shops' => $this->whenLoaded('branchShops', function () {
                return BranchShopResource::collection($this->branchShops);
            }),
            
            // Include primary branch shop
            'primary_branch_shop' => $this->when($this->relationLoaded('branchShops'), function () {
                $primaryBranch = $this->branchShops->where('pivot.is_primary', true)->first();
                return $primaryBranch ? new BranchShopResource($primaryBranch) : null;
            }),
        ];
    }
}
