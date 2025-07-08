<?php
namespace App\Repositories\Contact;

use App\Repositories\RepositoryInterface;

interface ContactRepositoryInterface extends RepositoryInterface
{
    //ví dụ: lấy 5 sản phầm đầu tiên
    public function getContact();
}