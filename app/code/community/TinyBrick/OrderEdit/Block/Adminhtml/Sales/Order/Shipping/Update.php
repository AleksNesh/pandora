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
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_Shipping_Update extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
	public $quote;
        /**
         * Gets the order from the sales order section
         * @return object $order
         */
	public function getOrder()
	{
		$orderId = $this->getRequest()->getParam('order_id');
		$order = Mage::getModel('sales/order')->load($orderId);
		return $order;
	}
	
	/**
         * Gets the order status from getOrder
         * @return string $status
         */
	public function getOrderStatus(){
		$order = $this->getOrder();
		$status = $order->getStatus();
		return $status;
	}
	/**
         * Gets the carriers you are allowed to ship too. 
         * @return array $storedRates
         */
	public function getShippingRateCollection($quoteId)
	{
		$rateCollection = Mage::getModel('orderedit/order_address_rate')->getCollection()->addFieldToFilter('order_id',$quoteId);
		$sortedRates = array();
		foreach($rateCollection as $rate){
			$sortedRates[$rate->getCarrierTitle()][] = array('rate_id' => $rate->getRateId(),'carrier' => $rate->getCarrier(), 'carrier_title' => $rate->getCarrierTitle(), 'code' => $rate->getCode(), 'method' => $rate->getMethod(), 'method_title' => $rate->getMethodTitle(), 'price' => $rate->getPrice());
		}
		return $sortedRates;
	}
	/**
         * Gets stores if you are using the store locator module
         * @return object
         */
	public function getStores()
	{
		return Mage::getModel('storelocator/storeLocator')->getCollection()->addFieldToFilter('status',1)->setOrder('title','asc');
	}
	/**
         * Gets the shipping rates for the order. Recalculates them
         * @return object $shippingRates
         */
	public function getShippingRates()
	{
		$shippingRates = $this->getShippingRateCollection();
		if(count($shippingRates)==0){
			Mage::getModel('orderedit/order_address')->recalculateShippingRates($this->getOrder());
			$shippingRates = $this->getShippingRateCollection();
		}		
		return $shippingRates;
	}
	/**
         * Gets the shipping rates based on the address
         * @param string $params Shipping address
         * @return object $shippingRates
         */
	public function getShippingAddressRates($params)
	{
		$order = $this->getOrder();
		$quoteId = $order->getQuoteId();
		$quote = Mage::getModel('sales/quote')->loadByIdWithoutStore($quoteId);
		
		//Remove any old rates that exist
		$oldRates = Mage::getModel('orderedit/order_address_rate')->getCollection()->addFieldToFilter('order_id',$quoteId);
		foreach($oldRates as $oldRate){$oldRate->delete();}
		
		//Get new rates
		Mage::getModel('orderedit/order_address')->recalculateShippingRates($quote,$params);
		$shippingRates = $this->getShippingRateCollection($quoteId);
		//Mage::helper('orderedit')->deleteQuote($this->quote->getEntityId());
		return $shippingRates;
	}
	/**
         * Gets the format the pricing should be in
         * @param int $price Number needing to be formated
         * @return string
         */
	public function getFormattedPrice($price)
	{
		return Mage::helper('core')->formatCurrency($price);
	}
	
}