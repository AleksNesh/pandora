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
class TinyBrick_OrderEdit_Model_Order_Address_Rate extends Mage_Shipping_Model_Rate_Abstract
{
    /**
     * @var string $_address Address of order
     */
    protected $_address;
    /**
     * Construct the rate model
     */
    protected function _construct()
    {
        $this->_init('orderedit/order_address_rate');
    }
    /**
     * Sets address id before saving
     * @return TinyBrick_OrderEdit_Model_Order_Address_Rate 
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getAddress()) {
            $this->setAddressId($this->getAddress()->getId());
        }
        return $this;
    }
    /**
     * Sets the address for the order
     * @param TinyBrick_OrderEdit_Model_Order_Address $address
     * @return TinyBrick_OrderEdit_Model_Order_Address_Rate 
     */
    public function setAddress(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $this->_address = $address;
        return $this;
    }
    /**
     * Gets the address
     * @return type 
     */
    public function getAddress()
    {
        return $this->_address;
    }
    /**
     * Imports the shipping rate into the total
     * @param Mage_Shipping_Model_Rate_Result_Abstract $rate
     * @param int $orderId
     * @param int $addressId
     * @return TinyBrick_OrderEdit_Model_Order_Address_Rate 
     */
    public function importShippingRate(Mage_Shipping_Model_Rate_Result_Abstract $rate,$orderId,$addressId)
    {
    	//Might need to be enabled if the rate quotes don't come back correctly
//        if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
//            $this
//                ->setCode($rate->getCarrier().'_error')
//                ->setCarrier($rate->getCarrier())
//                ->setCarrierTitle($rate->getCarrierTitle())
//                ->setErrorMessage($rate->getErrorMessage())
//            ;
//        } elseif ($rate instanceof Mage_Shipping_Model_Rate_Result_Method) {
            $this
            	->setCreatedAt(now())
            	->setUpdatedAt(now())
            	->setAddressId($addressId)
            	->setOrderId($orderId)
                ->setCode($rate->getCarrier().'_'.$rate->getMethod())
                ->setCarrier($rate->getCarrier())
                ->setCarrierTitle($rate->getCarrierTitle())
                ->setMethod($rate->getMethod())
                ->setMethodTitle($rate->getMethodTitle())
                ->setMethodDescription($rate->getMethodDescription())
                ->setPrice($rate->getPrice())
                ->save()
            ;
//        }
        return $this;
    }
}