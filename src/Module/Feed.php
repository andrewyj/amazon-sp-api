<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Feed getFeeds()
 * @method Feed createFeed()
 * @method Feed getFeed($feedId)
 * @method Feed cancelFeed($feedId)
 * @method Feed createFeedDocument()
 * @method Feed getFeedDocument($feedDocumentId)
 */
class Feed extends Requester
{
    protected $moduleName = 'feeds';
    protected $version = '2020-09-04';
}
