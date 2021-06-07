<?php

return [
    '2020-09-04' => [
        'getFeeds' => [
            'path' => '/feeds/2020-09-04/feeds',
            'method' => 'GET',
            'query_params' => [
                'feedTypes' => 'array|max:10|min:1',
                'feedTypes.*' => 'string',
                'marketplaceIds' => 'array|max:10|min:1',
                'marketplaceIds.*' => 'string',
                'pageSize' => 'integer|min:1|max|100',
                'processingStatuses' => 'array|min:1',
                'processingStatuses.*' => 'string|in:CANCELLED,DONE,FATAL,IN_PROGRESS,IN_QUEUE',
                'createdSince' => 'dateISO8601',
                'createdUntil' => 'dateISO8601',
                'nextToken' => 'string',
            ],
            'rate_limit' => [
                'rate'  => 0.0222,
                'burst' => 10
            ]
        ],
        'createFeed' => [
            'path' => '/feeds/2020-09-04/feeds',
            'method' => 'POST',
            'form_params' => [
                'feedType' => 'required|string',
                'marketplaceIds' => 'required|array',
                'inputFeedDocumentId' => 'required|string',
                'marketplaceIds.*' => 'string',
                'feedOptions' => 'array',
            ],
            'rate_limit' => [
                'rate'  => 0.0083,
                'burst' => 15
            ]
        ],
        'getFeed' => [
            'path' => '/feeds/2020-09-04/feeds/{feedId}',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 2,
                'burst' => 15
            ]
        ],
        'cancelFeed' => [
            'path' => '/feeds/2020-09-04/feeds/{feedId}',
            'method' => 'DELETE',
            'rate_limit' => [
                'rate'  => 0.0222,
                'burst' => 10
            ]
        ],
        'createFeedDocument' => [
            'path' => '/feeds/2020-09-04/documents',
            'method' => 'POST',
            'form_params' => [
                'contentType' => 'required|string',
            ],
            'rate_limit' => [
                'rate'  => 0.0083,
                'burst' => 15
            ]
        ],
        'getFeedDocument' => [
            'path' => '/feeds/2020-09-04/documents/{feedDocumentId}',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 0.0222,
                'burst' => 10
            ]
        ],
    ],
    '2021-06-30' => [
        'getFeeds' => [
            'path' => '/feeds/2021-06-30/feeds',
            'method' => 'GET',
            'query_params' => [
                'feedTypes' => 'array|max:10|min:1',
                'feedTypes.*' => 'string',
                'marketplaceIds' => 'array|max:10|min:1',
                'marketplaceIds.*' => 'string',
                'pageSize' => 'integer|min:1|max|100',
                'processingStatuses' => 'array|min:1',
                'processingStatuses.*' => 'string|in:CANCELLED,DONE,FATAL,IN_PROGRESS,IN_QUEUE',
                'createdSince' => 'dateISO8601',
                'createdUntil' => 'dateISO8601',
                'nextToken' => 'string',
            ],
            'rate_limit' => [
                'rate'  => 0.0222,
                'burst' => 10
            ]
        ],
        'createFeed' => [
            'path' => '/feeds/2021-06-30/feeds',
            'method' => 'POST',
            'form_params' => [
                'feedType' => 'required|string',
                'marketplaceIds' => 'required|array',
                'marketplaceIds.*' => 'string',
                'inputFeedDocumentId' => 'required|string',
                'feedOptions' => 'array',
            ],
            'rate_limit' => [
                'rate'  => 0.0083,
                'burst' => 15
            ]
        ],
        'getFeed' => [
            'path' => '/feeds/2021-06-30/feeds/{feedId}',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 2,
                'burst' => 15
            ]
        ],
        'cancelFeed' => [
            'path' => '/feeds/2021-06-30/feeds/{feedId}',
            'method' => 'DELETE',
            'rate_limit' => [
                'rate'  => 0.0222,
                'burst' => 10
            ]
        ],
        'createFeedDocument' => [
            'path' => '/feeds/2021-06-30/documents',
            'method' => 'POST',
            'form_params' => [
                'contentType' => 'required|string',
            ],
            'rate_limit' => [
                'rate'  => 0.0083,
                'burst' => 15
            ]
        ],
        'getFeedDocument' => [
            'path' => '/feeds/2021-06-30/documents/{feedDocumentId}',
            'method' => 'GET',
            'rate_limit' => [
                'rate'  => 0.0222,
                'burst' => 10
            ]
        ],
    ]
];
