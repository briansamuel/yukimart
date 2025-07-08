<?php
namespace App\Repositories\Setting;

use App\Repositories\BaseRepository;
use App\Repositories\Setting\SettingRepositoryInterface;
class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Setting::class;
    }

    public function findByName(string $settingName) {
        return $this->findByKey('setting_key', $settingName);
    }
    
    public function deletebyCondition($condition = []) {
        return $this->model->where($condition)->delete();
    }
}