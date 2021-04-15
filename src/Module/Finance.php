<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Finance listFinancialEventGroups()
 * @method Finance listFinancialEventsByGroupId()
 * @method Finance listFinancialEventsByOrderId($orderId)
 * @method Finance listFinancialEvents()
 */
class Finance extends Requester
{
    protected $moduleName = 'finances';
}
