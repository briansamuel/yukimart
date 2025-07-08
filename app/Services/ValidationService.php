<?php

namespace App\Services;


use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;

class ValidationService
{

    /*
    * function make validation
    */
    public function make($params, $type)
    {

        $validator = Validator::make(
            $params,
            $this->getRules($type),
            $this->getCustomMessages(),
            $this->attributes()
        );
        return $validator;
    }

    /*
    * function get rule config
    */
    public function getRules($type)
    {
        $rules = [
            'update_my_profile_fields' => [
                'full_name' => self::getRule('require_field'),
            ],
            'change_password_fields' => [
                'password' => self::getRule('password'),
                'new_password' => self::getRule('password'),
                'confirm_new_password' => self::getRule('password'),
            ],
            'add_user_fields' => [
                'email' => self::getRule('email'),
                'username' => self::getRule('username'),
                'full_name' => self::getRule('full_name'),
                'password' => self::getRule('password')
            ],
            'edit_user_fields' => [
                'full_name' => self::getRule('full_name'),
                'status' => self::getRule('require_field')
            ],
            'add_agent_fields' => [
                'email' => self::getRule('email'),
                'username' => self::getRule('username'),
                'full_name' => self::getRule('full_name'),
                'password' => self::getRule('password'),
                'agent_address' => self::getRule('require_field'),
                'agent_phone' => self::getRule('phone_number'),
                'agent_birthday' => self::getRule('require_field'),
            ],
            'add_page_fields' => [
                'page_title' => self::getRule('page_unique'),
                'page_description' => self::getRule('require_field'),
                'page_content' => self::getRule('require_field'),

            ],
            'edit_page_fields' => [
                'page_title' => self::getRule('require_field'),
                'page_description' => self::getRule('require_field'),
                'page_content' => self::getRule('require_field'),

            ],
            'add_news_fields' => [
                'post_title' => self::getRule('require_field'),
                'post_description' => self::getRule('require_field'),
                'post_content' => self::getRule('require_field'),

            ],
            'edit_news_fields' => [
                'post_title' => self::getRule('require_field'),
                'post_description' => self::getRule('require_field'),
                'post_content' => self::getRule('require_field'),

            ],
            'add_project_fields' => [
                'project_name' => self::getRule('require_field'),
                // 'project_description' => self::getRule('require_field'),
                'project_framework' => self::getRule('require_field'),
                // 'project_logo' => self::getRule('require_field'),
                // 'project_type' => self::getRule('require_field'),
                // 'project_budget' => self::getRule('require_field'),
                'project_category' => self::getRule('require_field'),
            ],
            'edit_project_fields' => [
                'project_title' => self::getRule('require_field'),
                'project_description' => self::getRule('require_field'),
                'project_content' => self::getRule('require_field'),
                'project_thumbnail' => self::getRule('require_field'),
                'project_location' => self::getRule('require_field'),
            ],
            'add_partner_fields' => [
                'partner_title' => self::getRule('post_unique'),
                'partner_description' => self::getRule('require_field'),
                'partner_thumbnail' => self::getRule('require_field'),
            ],
            'edit_partner_fields' => [
                'post_title' => self::getRule('require_field'),
                'post_description' => self::getRule('require_field'),
                'post_content' => self::getRule('require_field'),
            ],
            'add_recruitment_fields' => [
                'post_title' => self::getRule('post_unique'),
                'post_description' => self::getRule('require_field'),
                'post_content' => self::getRule('require_field'),
            ],
            'edit_recruitment_fields' => [
                'post_title' => self::getRule('require_field'),
                'post_description' => self::getRule('require_field'),
                'post_content' => self::getRule('require_field'),
            ],
            'add_category_fields' => [
                'category_name' => self::getRule('require_field'),
                'category_description' => self::getRule('require_field'),
                'category_parent' => self::getRule('require_field'),
                'language' => self::getRule('require_field'),
                'category_type' => self::getRule('require_field'),

            ],
            'add_user_group_fields' => [
                'group_name' => self::getRule('require_field'),
                // 'enabled' => self::getRule('require_field'),
                'short_description' => self::getRule('require_field')
            ],
            'add_host_fields' => [
                'host_name' => self::getRule('host_name'),
                'host_description' => self::getRule('require_field'),
                'host_convenient' => self::getRule('require_field'),
                'host_address' => self::getRule('require_field'),
                'host_lat' => self::getRule('require_field'),
                'host_lng' => self::getRule('require_field'),
                'province_name' => self::getRule('require_field'),
                'district_name' => self::getRule('require_field'),
                'created_by_agent' => self::getRule('require_field'),
            ],
            'edit_host_fields' => [
                'host_name' => self::getRule('require_field'),
                'host_description' => self::getRule('require_field'),
                'host_convenient' => self::getRule('require_field'),
                'host_address' => self::getRule('require_field'),
                'host_lat' => self::getRule('require_field'),
                'host_lng' => self::getRule('require_field'),
                'province_name' => self::getRule('require_field'),
                'district_name' => self::getRule('require_field'),
                'created_by_agent' => self::getRule('require_field'),
            ],

            'add_booking_fields' => [
                'host_id' => self::getRule('require_field'),
                'room_id' => self::getRule('require_field'),
                'guest_id' => self::getRule('require_field'),
                'checkin_date' => self::getRule('require_field'),
                'checkout_date' => self::getRule('require_field'),
                'booking_price' => self::getRule('require_field'),
                'night_booking' => self::getRule('require_field'),
                'created_by_agent' => self::getRule('require_field'),
            ],


            'add_review_fields' => [
                'review_title' => self::getRule('require_field'),
                'review_content' => self::getRule('require_field'),
                'rating_review' => self::getRule('require_field'),
                'host_id' => self::getRule('require_field'),
                'created_by_guest' => self::getRule('require_field'),
            ],



            'edit_contact_fields' => [
                'status' => self::getRule('require_field')
            ],
            'add_contact_reply_fields' => [
                'contact_id' => self::getRule('require_field'),
                'message' => self::getRule('require_field')
            ],
            'edit_comment_fields' => [
                'comment_status' => self::getRule('require_field')
            ],
            'edit_review_fields' => [
                'review_status' => self::getRule('require_field')
            ],
            'add_top_deals_fields' => [
                'title' => self::getRule('require_field'),
                'image' => self::getRule('require_field'),
                'description' => self::getRule('require_field'),
                'label' => self::getRule('require_field'),
                'location' => self::getRule('require_field'),
                'start_time' => self::getRule('require_field'),
                'end_time' => self::getRule('require_field'),
                'regular_price' => self::getRule('require_field'),
                'sale_price' => self::getRule('require_field'),
                'link' => self::getRule('require_field'),
                'position' => self::getRule('require_field'),
                'language' => self::getRule('require_field'),
                'deal_group' => self::getRule('require_field'),
            ],
            'edit_top_deals_fields' => [
                'title' => self::getRule('require_field'),
                'description' => self::getRule('require_field'),
                'label' => self::getRule('require_field'),
                'location' => self::getRule('require_field'),
                'start_time' => self::getRule('require_field'),
                'end_time' => self::getRule('require_field'),
                'regular_price' => self::getRule('require_field'),
                'sale_price' => self::getRule('require_field'),
                'link' => self::getRule('require_field'),
                'position' => self::getRule('require_field'),
                'language' => self::getRule('require_field'),
                'deal_group' => self::getRule('require_field'),
            ],
            'add_banner_fields' => [
                'title' => self::getRule('require_field'),
                'image' => self::getRule('require_field'),
                'description' => self::getRule('require_field'),
                'link' => self::getRule('require_field'),
                'position' => self::getRule('require_field'),
                'banner_type' => self::getRule('require_field'),
                'language' => self::getRule('require_field'),
            ],
            'edit_banner_fields' => [
                'title' => self::getRule('require_field'),
                'description' => self::getRule('require_field'),
                'link' => self::getRule('require_field'),
                'position' => self::getRule('require_field'),
                'banner_type' => self::getRule('require_field'),
                'language' => self::getRule('require_field'),
                'caption' => self::getRule('require_field'),
                'type' => self::getRule('require_field'),
            ],
            'edit_subcribe_emails_fields' => [
                'email' => self::getRule('email'),
                'active' => self::getRule('require_field')
            ],
            'add_frontend_agent_fields' => [
                'name' => self::getRule('require_field'),
                'phone_number' => self::getRule('require_field'),
                'email' => self::getRule('email'),
                'content' => self::getRule('require_field'),
            ],
            'add_frontend_subcribe_email_fields' => [
                'email' => self::getRule('email'),
            ],
            'add_product_fields' => [
                'product_name' => self::getRule('require_field'),
                'product_description' => self::getRule('require_field'),
                'product_content' => self::getRule('require_field'),
                'sku' => self::getRule('sku_unique'),
                'cost_price' => self::getRule('price'),
                'sale_price' => self::getRule('price'),
                'product_status' => self::getRule('require_field'),
                'product_type' => self::getRule('require_field'),
            ],
            'edit_product_fields' => [
                'product_name' => self::getRule('require_field'),
                'product_description' => self::getRule('require_field'),
                'product_content' => self::getRule('require_field'),
                'sku' => self::getRule('require_field'),
                'cost_price' => self::getRule('price'),
                'sale_price' => self::getRule('price'),
                'product_status' => self::getRule('require_field'),
                'product_type' => self::getRule('require_field'),
            ],
            'inventory_transaction_fields' => [
                'product_id' => self::getRule('require_field'),
                'transaction_type' => self::getRule('require_field'),
                'quantity' => self::getRule('require_field'),
            ],
            'inventory_adjustment_fields' => [
                'product_id' => self::getRule('require_field'),
                'new_quantity' => self::getRule('number'),
                'reason' => self::getRule('require_field'),
            ],
            'stock_reservation_fields' => [
                'product_id' => self::getRule('require_field'),
                'quantity' => self::getRule('require_field'),
            ],
            'add_supplier_fields' => [
                'name' => self::getRule('require_field'),
                'code' => self::getRule('supplier_code_unique'),
                'email' => self::getRule('email_optional'),
                'phone' => self::getRule('phone_optional'),
                'status' => self::getRule('require_field'),
            ],
            'edit_supplier_fields' => [
                'name' => self::getRule('require_field'),
                'email' => self::getRule('email_optional'),
                'phone' => self::getRule('phone_optional'),
                'status' => self::getRule('require_field'),
            ],
            'order_fields' => [
                // 'customer.name' => self::getRule('require_field'),
                // 'customer.phone' => self::getRule('phone_optional'),
                // 'customer.email' => self::getRule('email_optional'),
                'channel' => self::getRule('require_field'),
                'status' => self::getRule('require_field'),
                'delivery_status' => self::getRule('require_field'),
                'items' => self::getRule('require_field'),
            ]
        ];

        return isset($rules[$type]) ? $rules[$type] : array();
    }

    /*
    * function get Attrbutes
    */
    public function attributes()
    {
        return [

            'post_title' => trans('admin.pages.title'),
            'post_description' =>  trans('admin.pages.summary'),
            'post_content' =>  trans('admin.pages.content'),
            'host_name' =>  trans('admin.hosts.host_name'),
            'host_descrition' => trans('admin.hosts.host_description'),
            'product_name' => 'Product Name',
            'product_description' => 'Product Description',
            'product_content' => 'Product Content',
            'sku' => 'SKU',
            'cost_price' => 'Cost Price',
            'sale_price' => 'Sale Price',
            'product_status' => 'Product Status',
            'product_type' => 'Product Type',
            'stock_quantity' => 'Stock Quantity',
            'reserved_quantity' => 'Reserved Quantity',
            'reorder_point' => 'Reorder Point',
            'track_inventory' => 'Track Inventory',
            'allow_backorder' => 'Allow Backorder',
            'max_order_quantity' => 'Maximum Order Quantity',
            'min_order_quantity' => 'Minimum Order Quantity',
            'supplier_name' => 'Supplier Name',
            'supplier_sku' => 'Supplier SKU',
            'supplier_cost' => 'Supplier Cost',
            'lead_time_days' => 'Lead Time (Days)',
            'transaction_type' => 'Transaction Type',
            'quantity' => 'Quantity',
            'new_quantity' => 'New Quantity',
            'reason' => 'Reason',
            'unit_cost' => 'Unit Cost',
            'notes' => 'Notes',
            'name' => 'Supplier Name',
            'code' => 'Supplier Code',
            'phone' => 'Phone Number',
            'email' => 'Email Address',
            'company' => 'Company Name',
            'tax_code' => 'Tax Code',
            'address' => 'Address',
            'province' => 'Province',
            'district' => 'District',
            'ward' => 'Ward',
            'branch_id' => 'Branch',
            'group' => 'Supplier Group',
            'note' => 'Notes',
            'status' => 'Status',

        ];
    }

    /*
    * function get rule
    */
    public function getRule($rule)
    {
        $rules = [
            'limit' => 'numeric',
            'offset' => 'numeric',
            'user_id' => 'numeric|required',
            'username' => 'required',
            'full_name' => 'required',
            'phone_number' => 'digits_between:10,15|required',
            'password' => 'nullable|min:5|required',
            'require_field' => 'required',
            'email' => 'email',
            'payment_method' => 'required',
            'ip' => 'ip',
            'amount' => 'numeric|required',
            'number' => 'numeric',
            'post_unique' => 'required|unique:posts',
            'page_unique' => 'required|unique:pages',
            'host_name' => 'required|unique:hosts',
            'sku_unique' => 'required|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'supplier_code_unique' => 'nullable|unique:suppliers,code',
            'email_optional' => 'nullable|email',
            'phone_optional' => 'nullable|digits_between:10,15'
        ];

        return $rules[$rule];
    }

    /**
     * function get custom messages
     */
    public function getCustomMessages()
    {
        $messages = [
            'required' => trans('validation.required'),
            'unique' => trans('validation.unique'),
        ];

        return $messages;
    }
}
