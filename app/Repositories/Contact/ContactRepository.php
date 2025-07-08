<?php
namespace App\Repositories\Contact;

use App\Repositories\BaseRepository;

class ContactRepository extends BaseRepository implements ContactRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Contacts::class;
    }

    public function getContact()
    {
        return $this->model->select('product_name')->take(5)->get();
    }
}