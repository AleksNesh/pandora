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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Eitems extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    /** 
     * Edits existing items of the order
     * @param TinyBrick_OrderEdit_Model_Order $order
     * @param array $data
     * @return boolean 
     */
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{
		$comment = "";
		foreach($data['id'] as $key => $itemId) {
			$item = $order->getItemById($itemId);
			
			$product = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('sku', $item->getSku())
			->addAttributeToSelect('*')
			->getFirstItem();
			
			if($data['remove'][$key]) {
				$comment .= "Removed Item(SKU): " . $item->getSku() . "<br />";
				
				$oldQty = $item->getQtyOrdered();
				$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
				$stockItem->addQty($oldQty);
				$stockItem->save();				
				
				$order->removeItem($itemId);
			} else {
				$oldArray = array('price'=>$item->getPrice(), 'discount'=>$item->getDiscountAmount(), 'qty'=>$item->getQtyOrdered());
				$item->setPrice($data['price'][$key]);
				$item->setBasePrice($data['price'][$key]);
				$item->setBaseOriginalPrice($data['price'][$key]);
				$item->setOriginalPrice($data['price'][$key]);
				$item->setBaseRowTotal($data['price'][$key]);
				if($data['discount'][$key]) {
					$item->setDiscountAmount($data['discount'][$key]);
					$item->setBaseDiscountAmount($data['discount'][$key]);
				}
				
				if($data['qty'][$key]) {
					$oldQty = $item->getQtyOrdered();
					$item->setQtyOrdered($data['qty'][$key]);
					$newQty = $item->getQtyOrdered();
					
					if($newQty > $oldQty) {
						$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
						$stockItem->subtractQty($newQty-$oldQty);
						$stockItem->save();
					}
					else if ($newQty < $oldQty) {
						$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
						$stockItem->addQty($oldQty-$newQty);
						$stockItem->save();
					}
				}
				$item->save();
				$newArray = array('price'=>$item->getPrice(), 'discount'=>$item->getDiscountAmount(), 'qty'=>$item->getQtyOrdered());
				if($newArray['price'] != $oldArray['price'] || $newArray['discount'] != $oldArray['discount'] || $newArray['qty'] != $oldArray['qty']) {
					$comment = "Edited item " . $item->getSku() . "<br />";
					if($newArray['price'] != $oldArray['price']) {
						$comment .= "Price FROM: " . $oldArray['price'] . " TO: " . $newArray['price'] . "<br />";
					}
					if($newArray['discount'] != $oldArray['discount']) {
						$comment .= "Discount FROM: " . $oldArray['discount'] . " TO: " . $newArray['discount'] . "<br />";
					}
					if($newArray['qty'] != $oldArray['qty']) {
						$comment .= "Qty FROM: " . $oldArray['qty'] . " TO: " . $newArray['qty'] . "<br />";
					}
				}

			}
		}
		if($comment != "") {
			$comment = "Edited items:<br />" . $comment . "<br />";
			return $comment;
		}
		return true;
	}
}