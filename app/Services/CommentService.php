<?php

namespace App\Services;

use App\Models\CommentModel;
use App\Transformers\ContactTransformer;

class CommentService
{

    public static function totalRows() {
        $result = CommentModel::totalRows();
        return $result;
    }

    public static function add($params)
    {
        $params['created_at'] = date("Y-m-d H:i:s");
        $params['updated_at'] = date("Y-m-d H:i:s");
        return CommentModel::add($params);
    }

    public function edit($id, $params)
    {
        $params['updated_at'] = date("Y-m-d H:i:s");
        return CommentModel::update($id, $params);
    }

    public function deleteMany($ids)
    {
        return CommentModel::deleteMany($ids);
    }

    public function updateMany($ids, $data)
    {
        return CommentModel::updateMany($ids, $data);
    }

    public function delete($ids)
    {
        return CommentModel::delete($ids);
    }

    public function detail($id)
    {
        $detail = CommentModel::findById($id);
        $news = PostService::findByKey('id', $detail->post_id);
        if($news) {
            $detail->post_title = $news->post_title;
        } else {
            $detail->post_title = 'Không tìm thấy bài viết';
        }
        return ContactTransformer::transformItem($detail);
    }

    public function getList(array $params)
    {
        $pagination = $params['pagination'];
        $sort = isset($params['sort']) ? $params['sort'] : [];
        $query = isset($params['query']) ? $params['query'] : [];
		$total = self::totalRows($query);

        $result = CommentModel::getMany($pagination, $sort, $query);
        foreach($result as $comment) {
            $news = PostService::findByKey('id', $comment->post_id);
            if($news) {
                $comment->post_title = $news->post_title;
            } else {
                $comment->post_title = 'Không tìm thấy bài viết';
            }
        }

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

    public static function takeNew($quantity)
    {
        return CommentModel::takeNew($quantity);
    }

}
