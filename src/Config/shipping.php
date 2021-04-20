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
        )
    ],
    'getShipment' => [
        'path' => '/shipping/v1/shipments/{shipmentId}',
        'method' => 'GET',
    ],
    'cancelShipment' => [
        'path' => '/shipping/v1/shipments/{shipmentId}/cancel',
        'method' => 'POST',
    ],
    'purchaseLabels' => [
        'path' => '/shipping/v1/shipments/{shipmentId}/purchaseLabels',
        'method' => 'POST',
        'form_params' => array_merge([
            'rateId' => 'required|string',
        ], Schemas::labelSpecification('labelSpecification')),
    ],
    'retrieveShippingLabel' => [
        'path' => '/shipping/v1/shipments/{shipmentId}/containers/{trackingId}/label',
        'method' => 'POST',
        'form_params' => Schemas::labelSpecification('labelSpecification')
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
        )
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
        )
    ],
    'getAccount' => [
        'path' => '/shipping/v1/account',
        'method' => 'GET',
    ],
    'getTrackingInformation' => [
        'path' => '/shipping/v1/tracking/{trackingId}',
        'method' => 'GET',
    ],
];
