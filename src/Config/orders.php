<?php

return [
    'getOrders' => [
        'path' => '/orders/v0/orders',
        'method' => 'GET',
        'query_params' => [
            'CreatedAfter' => 'string',
            'CreatedBefore' => 'string',
            'LastUpdatedAfter' => 'string',
            'LastUpdatedBefore' => 'string',
            'OrderStatuses' => 'array',
            'MarketplaceIds' => 'required|array|max:50',
            'FulfillmentChannels' => 'array',
            'PaymentMethods' => 'array',
            'BuyerEmail' => 'string',
            'SellerOrderId' => 'string',
            'MaxResultsPerPage' => 'integer',
            'EasyShipShipmentStatuses' => 'array',
            'NextToken' => 'string',
            'AmazonOrderIds' => 'array',
        ]
    ],
    'getOrder' => [
        'path' => '/orders/v0/orders/{orderId}',
        'method' => 'GET',
    ],
    'getOrderBuyerInfo' => [
        'path' => '/orders/v0/orders/{orderId}/buyerInfo',
        'method' => 'GET',
    ],
    'getOrderAddress' => [
        'path' => '/orders/v0/orders/{orderId}/address',
        'method' => 'GET',
    ],
    'getOrderItems' => [
        'path' => '/orders/v0/orders/{orderId}/orderItems',
        'method' => 'GET',
    ],
    'getOrderItemsBuyerInfo' => [
        'path' => '/orders/v0/orders/{orderId}/orderItems/buyerInfo',
        'method' => 'GET',
        'query_params' => [
            'NextToken' => 'string'
        ]
    ],

];
