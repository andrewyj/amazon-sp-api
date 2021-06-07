<?php

namespace AmazonSellingPartnerAPI\Module;

use AmazonSellingPartnerAPI\Exception\ModuleException;

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

    protected function getModuleName(): string
    {
        return $this->moduleName. $this->version;
    }

    /**
     * Call it before calling a operation if you need.
     *
     * @param string $version
     * @return $this
     * @throws ModuleException
     */
    public function setVersion(string $version): Feed
    {
        if (date('Y-m-d', strtotime($version)) !== $version) {
            throw new ModuleException('Invalid version: '. $version);
        }
        $this->version = $version;

        return $this;
    }
}
