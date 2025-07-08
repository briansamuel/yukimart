<?php
namespace App\Repositories\Product;

use App\Repositories\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    //ví dụ: lấy 5 sản phầm đầu tiên
    public function getProduct();

    /**
     * Search products with filters and pagination
     */
    public function search($keyword = null, $filter = [], $limit = 20, $offset = 0, $sort = [], $column = ['*']);

    /**
     * Get total count of products matching search criteria
     */
    public function totalRow($keyword = null, $filter = []);

    /**
     * Get newest products
     */
    public function takeNew($quantity, $filter = []);
}
