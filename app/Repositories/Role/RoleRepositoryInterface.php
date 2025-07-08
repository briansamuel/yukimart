<?php
namespace App\Repositories\Role;

use App\Repositories\RepositoryInterface;

interface RoleRepositoryInterface extends RepositoryInterface
{
    // Get Role Active
    public function getRoleActive();

    // Get Role by ID
    public function getRoleById($ids);
}
