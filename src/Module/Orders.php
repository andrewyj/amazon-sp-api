<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Orders getOrders()
 * @method Orders getOrder($orderId)
 * @method Orders getOrderBuyerInfo($orderId)
 * @method Orders getOrderAddress($orderId)
 * @method Orders getOrderItems($orderId)
 * @method Orders getOrderItemsBuyerInfo($orderId)
 */
class Orders extends Requester
{
    protected $moduleName = 'orders';
}
