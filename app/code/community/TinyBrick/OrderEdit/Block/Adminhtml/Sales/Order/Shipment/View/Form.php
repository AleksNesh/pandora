<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_Shipment_View_Form extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Form
{
    /**
     * Checks if you can create a shipping label
     * @return boolean
     */
    public function canCreateShippingLabel()
    {
            $carrier = $this->getOrder()->getShippingCarrier();
            if(method_exists($carrier, 'isShippingLabelsAvailable')) {
                return $carrier->isShippingLabelsAvailable();
            } else {
                return false;
            }
    }
}
