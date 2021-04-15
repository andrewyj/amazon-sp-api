<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Order getOrders()
 * @method Order getOrder($orderId)
 * @method Order getOrderBuyerInfo($orderId)
 * @method Order getOrderAddress($orderId)
 * @method Order getOrderItems($orderId)
 * @method Order getOrderItemsBuyerInfo($orderId)
 */
class Order extends Requester
{
    protected $moduleName = 'orders';
}
