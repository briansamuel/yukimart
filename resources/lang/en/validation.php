<?php

//
return array (

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */
    'required' => 'The :attribute field is required.',
    'unique' => 'The :attribute has already been taken.',
    'numeric' => 'The :attribute must be a number.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'string' => 'The :attribute may not be greater than :max characters.',
    ],
    'email' => 'The :attribute must be a valid email address.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'custom' => [
        'product_name' => [
            'required' => 'Product name is required and cannot be empty.',
        ],
        'product_description' => [
            'required' => 'Product description is required to help customers understand your product.',
        ],
        'product_content' => [
            'required' => 'Product content is required for detailed product information.',
        ],
        'sku' => [
            'required' => 'SKU (Stock Keeping Unit) is required for inventory management.',
            'unique' => 'This SKU is already used by another product. Please choose a different SKU.',
        ],
        'cost_price' => [
            'required' => 'Cost price is required for profit calculation.',
            'numeric' => 'Cost price must be a valid number.',
            'min' => 'Cost price must be at least 0.',
        ],
        'sale_price' => [
            'required' => 'Sale price is required for customer pricing.',
            'numeric' => 'Sale price must be a valid number.',
            'min' => 'Sale price must be at least 0.',
        ],
        'product_status' => [
            'required' => 'Product status is required to control product visibility.',
        ],
        'product_type' => [
            'required' => 'Product type is required to categorize your product.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */
    'attributes' => [
        'product_name' => 'Product Name',
        'product_description' => 'Product Description',
        'product_content' => 'Product Content',
        'sku' => 'SKU',
        'cost_price' => 'Cost Price',
        'sale_price' => 'Sale Price',
        'product_status' => 'Product Status',
        'product_type' => 'Product Type',
        'stock_quantity' => 'Stock Quantity',
        'weight' => 'Weight',
        'points' => 'Points',
        'reorder_point' => 'Reorder Point',
        'product_feature' => 'Featured Product',
        'product_thumbnail' => 'Product Thumbnail',
    ],
)

?>