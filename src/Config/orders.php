<?php

return [
    'v0' => [
        'getOrders' => [
            'path' => '/orders/v0/orders',
            'method' => 'GET',
            'query_params' => [
                'CreatedAfter' => 'string|dateISO8601|required_without:LastUpdatedAfter',
                'CreatedBefore' => 'string|dateISO8601',
                'LastUpdatedAfter' => 'string|dateISO8601|required_without:CreatedAfter',
                'LastUpdatedBefore' => 'string|dateISO8601',
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
            ],
            'rate_limit' => [
                'rate'  => 0.0055,
                'burst' => 20
            ]
        ],
        'getOrder' => [
            'path' => '/orders/v0/orders/{orderId}',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 0.0055,
                'burst' => 20
            ]
        ],
        'getOrderBuyerInfo' => [
            'path' => '/orders/v0/orders/{orderId}/buyerInfo',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 0.0055,
                'burst' => 20
            ]
        ],
        'getOrderAddress' => [
            'path' => '/orders/v0/orders/{orderId}/address',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 0.0055,
                'burst' => 20
            ]
        ],
        'getOrderItems' => [
            'path' => '/orders/v0/orders/{orderId}/orderItems',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 0.0055,
                'burst' => 20
            ]
        ],
        'getOrderItemsBuyerInfo' => [
            'path' => '/orders/v0/orders/{orderId}/orderItems/buyerInfo',
            'method' => 'GET',
            'query_params' => [
                'NextToken' => 'string'
            ],
            'rate_limit' => [
                'rate'  => 0.0055,
                'burst' => 20
            ]
        ],
    ]
];
