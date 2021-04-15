<?php

return [
    'listFinancialEventGroups' => [
        'path' => '/finances/v0/financialEventGroups',
        'method' => 'GET',
        'query_params' => [
            'MaxResultsPerPage' => 'integer',
            'FinancialEventGroupStartedBefore' => 'string',
            'FinancialEventGroupStartedAfter' => 'string',
            'NextToken' => 'string',
        ]
    ],
    'listFinancialEventsByGroupId' => [
        'path' => '/finances/v0/financialEventGroups/{eventGroupId}/financialEvents',
        'method' => 'GET',
        'query_params' => [
            'MaxResultsPerPage' => 'integer',
            'eventGroupId' => 'string',
            'NextToken' => 'string',
        ]
    ],
    'listFinancialEventsByOrderId' => [
        'path' => '/finances/v0/orders/{orderId}/financialEvents',
        'method' => 'GET',
        'query_params' => [
            'MaxResultsPerPage' => 'integer',
            'NextToken' => 'string',
        ]
    ],
    'listFinancialEvents' => [
        'path' => '/finances/v0/financialEvents',
        'method' => 'GET',
        'query_params' => [
            'MaxResultsPerPage' => 'integer',
            'PostedAfter' => 'string',
            'PostedBefore' => 'string',
            'NextToken' => 'string',
        ]
    ],
];
