<?php

return [
    'getPricing' => [
        'path' => '/products/pricing/v0/price',
        'method' => 'GET',
        'query_params' => [
            'MarketplaceId' => 'required|string',
            'Asins' => 'array',
            'Skus' => 'array',
            'ItemType' => 'in:Asin,Sku',
            'ItemCondition' => 'in:New,Used,Collectible,Refurbished,Club',
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
        ]
    ],
    'getListingOffers' => [
        'path' => '/products/pricing/v0/listings/{SellerSKU}/offers',
        'method' => 'GET',
        'query_params' => [
            'MarketplaceId' => 'required|string',
            'ItemCondition' => 'in:New,Used,Collectible,Refurbished,Club',
        ]
    ],
    'getItemOffers' => [
        'path' => '/products/pricing/v0/items/{Asin}/offers',
        'method' => 'GET',
        'query_params' => [
            'MarketplaceId' => 'required|string',
            'ItemCondition' => 'in:New,Used,Collectible,Refurbished,Club',
        ]
    ],

];
