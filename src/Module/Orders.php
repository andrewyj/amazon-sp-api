<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Orders getOrders()
 * @method Orders getOrder($orderId)
 */
class Orders extends Requester
{
    protected $moduleName = 'orders';
}
