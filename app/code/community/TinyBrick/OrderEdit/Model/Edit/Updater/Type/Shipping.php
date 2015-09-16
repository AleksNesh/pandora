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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Shipping extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    /**
     * Edits the shipping address of the order
     * @param TinyBrick_OrderEdit_Model_Order $order
     * @param array $data
     * @return string 
     */
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{
		$array = array();
		$shipping = $order->getShippingAddress();
		$oldArray = $shipping->getData();
		$data['street'] = $data['street1'];
		if($data['street2']) {
			$data['street'] .= "\n" . $data['street2'];
		}
		$shipping->setData($data);
		$region = Mage::getResourceModel('directory/region_collection')->addFieldToFilter('default_name', $data['region'])->getFirstItem();
		$shipping->setRegionId($region->getId());
		try{
			$shipping->save();
			$newArray = $shipping->getData();
			$results = array_diff($oldArray, $newArray);
			$count = 0;
			$comment = "";
			foreach($results as $key => $result) {
				if(array_key_exists($key, $newArray)) {
					if($key == 'updated_at'){
						
					}
					else{
						$comment .= "Changed " . $key . " FROM: " . $oldArray[$key] . " TO: " . $newArray[$key] . "<br />";
						$count++;
					}
				}
			}

			if($count != 0) {
				$comment = "Changed shipping address:<br />" . $comment . "<br />";
				return $comment;
			}
			return true;
		}catch(Exception $e){
			$array['status'] = 'error';
			$array['msg'] = "Error updating shipping address";
			return false;
		}
	}
}