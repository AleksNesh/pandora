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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Status extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    /**
     * Edits the status of the order
     * @param TinyBrick_OrderEdit_Model_Order $order
     * @param array $data
     * @return string 
     */
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{	
		$array = array();
		$oldStatus = $order->getStatusLabel();
		
		if($data['status_id'] != '') {
			$order->setStatus($data['status_id']);
		}
		try{
			$order->save();
			$newStatus = $order->getStatusLabel();
			$results = strcmp($oldStatus, $newStatus);
			if($results != 0) {
				$comment = "Changed Status:<br />";
				$comment .= "Changed FROM: " . $oldStatus . " TO: " . $newStatus . "<br /><br />";
				return $comment;
			}
			return true;
		}catch(Exception $e){
			$array['status'] = 'error';
			$array['msg'] = "Error updating status";
			return false;
		}
		return true;

	}
}