<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Gross cost as base cost
    |--------------------------------------------------------------------------
    |
    | This default value is used to select the method to calculate costs and taxes
    | If true the obj cost is managed as a gross cost, so taxes will be calculated by separation/exclusion
    |
    */

    'calculator' => \Gloudemans\ShoppingShopCart\Calculation\DefaultCalculator::class,

    /*
    |--------------------------------------------------------------------------
    | Default tax rate
    |--------------------------------------------------------------------------
    |
    | This default tax rate will be used when you make a class implement the
    | Taxable interface and use the HasTax trait.
    |
    */

    'tax' => 0,

    /*
    |--------------------------------------------------------------------------
    | ShoppingShopCart database settings
    |--------------------------------------------------------------------------
    |
    | Here you can set the connection that the shoppingShopCart should use when
    | storing and restoring a ShopCart.
    |
    */

    'database' => [

        'connection' => null,

        'table' => 'shoppingShopCart',

    ],

    /*
    |--------------------------------------------------------------------------
    | Destroy the ShopCart on user exit
    |--------------------------------------------------------------------------
    |
    | When this option is set to 'true' the ShopCart will automatically
    | destroy all ShopCart instances when the user logs out.
    |
    */

    'destroy_on_exit' => false,

    /*
    |--------------------------------------------------------------------------
    | Default number format
    |--------------------------------------------------------------------------
    |
    | This defaults will be used for the formatted numbers if you don't
    | set them in the method call.
    |
    */

    'format' => [

        'decimals' => 2,

        'decimal_point' => '.',

        'thousand_separator' => '',

    ],

];
