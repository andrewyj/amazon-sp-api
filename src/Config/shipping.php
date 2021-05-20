<?php

use AmazonSellingPartnerAPI\Schemas;

return [
    'createShipment' => [
        'path' => '/shipping/v1/shipments',
        'method' => 'POST',
        'form_params' => array_merge([
            'clientReferenceId'    => 'required|string|max:40',
        ],
            Schemas::address('shipTo'),
            Schemas::address('shipFrom'),
            Schemas::container('containers.*'),
        ),
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'getShipment' => [
        'path' => '/shipping/v1/shipments/{shipmentId}',
        'method' => 'GET',
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'cancelShipment' => [
        'path' => '/shipping/v1/shipments/{shipmentId}/cancel',
        'method' => 'POST',
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'purchaseLabels' => [
        'path' => '/shipping/v1/shipments/{shipmentId}/purchaseLabels',
        'method' => 'POST',
        'form_params' => array_merge([
            'rateId' => 'required|string',
        ], Schemas::labelSpecification('labelSpecification')),
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'retrieveShippingLabel' => [
        'path' => '/shipping/v1/shipments/{shipmentId}/containers/{trackingId}/label',
        'method' => 'POST',
        'form_params' => Schemas::labelSpecification('labelSpecification'),
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'purchaseShipment' => [
        'path' => ' /shipping/v1/purchaseShipment',
        'method' => 'POST',
        'form_params' => array_merge([
            'clientReferenceId' => 'required|string|max:40',
            'shipDate' => 'string',
            'serviceType' => 'required|in:Amazon Shipping Ground,Amazon Shipping Standard,Amazon Shipping Premium	',
        ],
            Schemas::address('shipTo'),
            Schemas::address('shipFrom'),
            Schemas::container('containers.*'),
            Schemas::labelSpecification('labelSpecification')
        ),
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'getRates' => [
        'path' => '/shipping/v1/rates',
        'method' => 'POST',
        'form_params' => array_merge([
            'shipDate' => 'string',
            'serviceType' => 'required|in:Amazon Shipping Ground,Amazon Shipping Standard,Amazon Shipping Premium	',
        ],
            Schemas::address('shipTo'),
            Schemas::address('shipFrom'),
            Schemas::containerSpecifications('containerSpecifications.*'),
        ),
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'getAccount' => [
        'path' => '/shipping/v1/account',
        'method' => 'GET',
        'rate_limit' => [
            'rate'  => 5,
            'burst' => 15
        ]
    ],
    'getTrackingInformation' => [
        'path' => '/shipping/v1/tracking/{trackingId}',
        'method' => 'GET',
        'rate_limit' => [
            'rate'  => 1,
            'burst' => 1
        ]
    ],
];
