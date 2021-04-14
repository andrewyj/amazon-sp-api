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
            'MarketplaceIds' => 'array|max:50',
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
        'path_params' => [
            'orderId' => 'required|string'
        ]
    ]
];
