<?php
namespace App\Repositories\Setting;

use App\Repositories\RepositoryInterface;

interface SettingRepositoryInterface extends RepositoryInterface
{
    // Find by name function

    public function findByName(string $name);

    // Delete by Condition 
    public function deletebyCondition($condition = []);
}