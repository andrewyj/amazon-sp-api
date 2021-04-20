<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Finance getPricing()
 * @method Finance getCompetitivePricing()
 * @method Finance getListingOffers($sellerSKU)
 * @method Finance getItemOffers($asin)
 */
class Product extends Requester
{
    protected $moduleName = 'products';
}
