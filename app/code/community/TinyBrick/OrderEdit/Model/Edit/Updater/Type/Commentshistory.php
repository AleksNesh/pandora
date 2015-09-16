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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Commentshistory extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    /**
     * Edits the comment history of an order
     * @param TinyBrick_OrderEdit_Model_Order $order
     * @param array $data Data to edit
     * @return boolean
     */
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{
		$comment = "";
		foreach($data['id'] as $key => $commentId) {
			
			$statusModel = Mage::getModel('sales/order_status_history');
			$statusModel->load($commentId);
			
			if($data['remove'][$key]) {
				$comment .= "CommentID: " . $commentId . "<br />";
				
				$statusModel->delete();
			}
			else {
				$statusModel->setComment($data['comment'][$key]);
				$statusModel->save();
			}

		}
		if($comment != "") {
			$comment = "Removed:<br />" . $comment . "<br />";
			return $comment;
		}
		return true;
	}
}
