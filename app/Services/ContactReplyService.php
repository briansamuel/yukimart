<?php

namespace App\Services;

use App\Models\ContactReplyModel;
use App\Transformers\ContactReplyTransformer;

class ContactReplyService
{

    public static function add($params)
    {
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return ContactReplyModel::add($params);
    }

    public function edit($id, $params)
    {
        $params['updated_at'] = date("Y-m-d H:i:s");
        return ContactReplyModel::update($id, $params);
    }

    public function deleteMany($ids)
    {
        return ContactReplyModel::deleteMany($ids);
    }

    public function updateMany($ids, $data)
    {
        return ContactReplyModel::updateMany($ids, $data);
    }

    public function delete($ids)
    {
        return ContactReplyModel::delete($ids);
    }

    public static function detail($id)
    {
        $detail = ContactReplyModel::findById($id);

        return ContactReplyTransformer::transformItem($detail);
    }

    public static function getByContactId($contact_id)
    {
        $result = ContactReplyModel::getByKey('contact_id', $contact_id);

        return ContactReplyTransformer::transformCollection($result);
    }

}
