<?php

namespace AmazonSellingPartnerAPI\Module;

/**
 * @method Shipping createShipment()
 * @method Shipping getShipment($shipmentId)
 * @method Shipping cancelShipment($shipmentId)
 * @method Shipping purchaseLabels($shipmentId)
 * @method Shipping retrieveShippingLabel($shipmentId, $trackingId)
 * @method Shipping purchaseShipment()
 * @method Shipping getRates()
 * @method Shipping getAccount()
 * @method Shipping getTrackingInformation($trackingId)
 */
class Shipping extends Requester
{
    protected $moduleName = 'shipping';
}
