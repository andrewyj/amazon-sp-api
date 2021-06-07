<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Catalog listCatalogItems()
 * @method Catalog getCatalogItem($asin)
 * @method Catalog listCatalogCategories()
 */
class Catalog extends Requester
{
    protected $moduleName = 'catalog';
}
