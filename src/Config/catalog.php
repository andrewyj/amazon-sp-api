<?php

return [
    'v0' => [
        'listCatalogItems' => [
            'path' => '/catalog/v0/items',
            'method' => 'GET',
            'query_params' => [
                'MarketplaceId' => 'required|string',
                'Query' => 'string',
                'QueryContextId' => 'string',
                'SellerSKU' => 'string',
                'UPC' => 'string',
                'EAN' => 'string',
                'ISBN' => 'string',
                'JAN' => 'string',
            ],
            'rate_limit' => [
                'rate'  => 6,
                'burst' => 40
            ]
        ],
        'getCatalogItem' => [
            'path' => '/catalog/v0/items/{asin}',
            'method' => 'GET',
            'query_params' => [
                'MarketplaceId' => 'required|string',
            ],
            'rate_limit' => [
                'rate'  => 2,
                'burst' => 20
            ]
        ],
        'listCatalogCategories' => [
            'path' => '/catalog/v0/categories',
            'method' => 'GET',
            'query_params' => [
                'MarketplaceId' => 'required|string',
                'ASIN' => 'string',
                'SellerSKU' => 'string',
            ],
            'rate_limit' => [
                'rate'  => 1,
                'burst' => 40
            ]
        ],
    ]
];
