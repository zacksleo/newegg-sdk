<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Verify Service Status
    |--------------------------------------------------------------------------
    |
    | @see https://developer.newegg.com/newegg_marketplace_api/verify_service_status/
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    |
    | @see https://developer.newegg.com/newegg_marketplace_api/order_management/
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Get China Order Information
    |--------------------------------------------------------------------------
    |
    | Retrieve China order info only by specified criteria.
    | Newegg.com: https://api.newegg.com/marketplace/ordermgmt/order/chinaorderinfo?sellerid={sellerid}
    | @see https://developer.newegg.com/newegg_marketplace_api/order_management/get-china-order-information/
    |
    */

    'marketplace.ordermgmt.order.chinaorderinfo' => 'GetChinaOrderInfoRequest',

    /*
    |--------------------------------------------------------------------------
    | RMA Management
    |--------------------------------------------------------------------------
    |
    | @see https://developer.newegg.com/newegg_marketplace_api/rma_management/
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Get RMA Information
    |--------------------------------------------------------------------------
    |
    | Get RMA information by specified query conditions.
    | Resource URL
    | Newegg.com: https://api.newegg.com/marketplace/servicemgmt/rma/rmainfo?sellerid={sellerid}&version={version}
    | Neweggbusiness.com: https://api.newegg.com/marketplace/b2b/servicemgmt/rma/rmainfo?sellerid={sellerid}&version={version}
    | Newegg.ca: https://api.newegg.com/marketplace/can/servicemgmt/rma/rmainfo?sellerid={sellerid}&version={version}
    | @see https://developer.newegg.com/newegg_marketplace_api/rma_management/
    |
    */
    'marketplace.servicemgmt.rma.rmainfo' => 'GetRMAInfoRequest',   //Get RMA Information
];
