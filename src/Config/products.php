<?php

return [
    'v0' => [
        'getPricing' => [
            'path' => '/products/pricing/v0/price',
            'method' => 'GET',
            'query_params' => [
                'MarketplaceId' => 'required|string',
                'Asins' => 'array',
                'Skus' => 'array',
                'ItemType' => 'in:Asin,Sku',
                'ItemCondition' => 'in:New,Used,Collectible,Refurbished,Club',
            ],
            'rate_limit' => [
                'rate'  => 10,
                'burst' => 20
            ]
        ],
        'getCompetitivePricing' => [
            'path' => '/products/pricing/v0/competitivePrice',
            'method' => 'GET',
            'query_params' => [
                'MarketplaceId' => 'required|string',
                'Asins' => 'array',
                'Skus' => 'array',
                'ItemType' => 'in:Asin,Sku',
            ],
            'rate_limit' => [
                'rate'  => 10,
                'burst' => 20
            ]
        ],
        'getListingOffers' => [
            'path' => '/products/pricing/v0/listings/{SellerSKU}/offers',
            'method' => 'GET',
            'query_params' => [
                'MarketplaceId' => 'required|string',
                'ItemCondition' => 'in:New,Used,Collectible,Refurbished,Club',
            ],
            'rate_limit' => [
                'rate'  => 5,
                'burst' => 10
            ]
        ],
        'getItemOffers' => [
            'path' => '/products/pricing/v0/items/{Asin}/offers',
            'method' => 'GET',
            'query_params' => [
                'MarketplaceId' => 'required|string',
                'ItemCondition' => 'in:New,Used,Collectible,Refurbished,Club',
            ],
            'rate_limit' => [
                'rate'  => 5,
                'burst' => 10
            ]
        ],
    ]
];
