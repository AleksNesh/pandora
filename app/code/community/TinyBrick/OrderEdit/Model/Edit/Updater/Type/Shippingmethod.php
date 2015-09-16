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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Shippingmethod extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    /**
     * Edits the shipping method of the order
     * @param TinyBrick_OrderEdit_Model_Order $order
     * @param array $data
     * @return string 
     */
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{	
		$array = array();
		$orderStatus = $order->getStatus();
		
		$oldMethod = $order->getShippingDescription()." - $".substr($order->getShippingAmount(),0,-2);
		if($data['customcarrier'] != '' && $data['rateid'] == 'custom') {
			$order->setShippingMethod('custom');
			$order->setShippingDescription($data['customcarrier']." - ".$data['customMethod']);
		} else {
			if($data['rateid'] != 'custom') {
				$shippingRate = Mage::getModel('orderedit/order_address_rate')->getCollection()->addFieldToFilter('rate_id',$data['rateid'])->getFirstItem();
				$order->setShippingMethod($shippingRate->getCode());
				$order->setShippingDescription($shippingRate->getCarrierTitle()." - ".$shippingRate->getMethodTitle());
			}
		}
		if($data['customPrice'] != '') {
			$order->setShippingAmount($data['customPrice']);
                        $order->setBaseShippingAmount($data['customPrice']);
		} else {
			if($data['rateid'] != 'custom') {
				$order->setShippingAmount($shippingRate->getPrice());
			}
		}
		try{
			$order->save();
			//$newMethod = $order->getShippingDescription()." - $".substr($order->getShippingAmount(),0,-2);
			$newMethod = $order->getShippingDescription()." - $".$order->getShippingAmount;
			$results = strcmp($oldMethod, $newMethod);
			if($results != 0) {
				$comment = "Changed shipping method:<br />";
				$comment .= "Changed FROM: " . $oldMethod . " TO: " . $newMethod . "<br /><br />";
				return $comment;
			}
			return true;
		}catch(Exception $e){
			$array['status'] = 'error';
			$array['msg'] = "Error updating shipping method";
			return false;
		}
		return true;

	}
}