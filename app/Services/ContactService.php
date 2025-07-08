<?php

namespace App\Services;

use App\Models\ContactModel;
use App\Repositories\Contact\ContactRepositoryInterface;
use App\Transformers\ContactTransformer;

class ContactService
{
    protected $contactRepo;

    public function __construct(ContactRepositoryInterface $contactRepo)
    {
        $this->contactRepo = $contactRepo;
    }


    public function totalRows() {
        $result = $this->contactRepo->count();
        return $result;
    }

    public function add($params)
    {
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return $this->contactRepo->create($params);
    }

    public function addFromFrontEnd($params)
    {
        $params['subject'] = 'Thông tin liên hệ của khách gửi từ Liên hệ';
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");

        return $this->contactRepo->create($params);
    }

    public function edit($id, $params)
    {
        $params['updated_at'] = date("Y-m-d H:i:s");
        return $this->contactRepo->update($id, $params);
    }

    public function deleteMany($ids)
    {
        return $this->contactRepo->deleteMany($ids);
    }

    public function updateMany($ids, $data)
    {
        return $this->contactRepo->updateMany($ids, $data);
    }

    public function delete($ids)
    {
        return $this->contactRepo->delete($ids);
    }

    public function detail($id)
    {
        $detail = $this->contactRepo->findByKey('id', $id);
        return $detail;
        // return ContactTransformer::transformItem($detail);
    }

    public function getList(array $params)
    {
        $total = self::totalRows();
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];

        $result = $this->contactRepo->getMany($pagination, $sort, $query);

        $data['data'] = $result;
        $data['meta']['page'] = isset($pagination['page']) ? $pagination['page'] : 1;
        $data['meta']['perpage'] = isset($pagination['perpage']) ? $pagination['perpage'] : 20;
        $data['meta']['total'] = $total;
        $data['meta']['pages'] = ceil($total / $data['meta']['perpage']);
        $data['meta']['rowIds'] = self::getListIDs($result);

        return $data;
    }

    public function getListIDs($data) {

        $ids = array();

        foreach($data as $row) {
            array_push($ids, $row->id);
        }

        return $ids;
    }

    public function takeNew($quantity)
    {
        return $this->contactRepo->getMany(0, $quantity, null);
    }

}