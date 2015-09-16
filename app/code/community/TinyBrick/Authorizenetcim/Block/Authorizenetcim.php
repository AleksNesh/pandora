<?php
/**
 * OpenCommerce Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OpenCommerce Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@OpenCommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future.
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_CIMple
 * @copyright  Copyright (c) 2013 OpenCommerce Inc. LLC
 * @license    http://store.opencommercellc.com/commercial-license
 */
class TinyBrick_Authorizenetcim_Block_Authorizenetcim extends Mage_Core_Block_Template
{
	
	public function getAction()
	{
		$name = Mage::app()->getRequest()->getParam('name', false);
		return Mage::getUrl('authorizenetcim/index/submit', array('name' => $name));
	}
	/**
	 * Gets the URL to edit the credit cards
	 * @param int $ccSaveID
	 * @return Ambigous <string, mixed>
	 */
	public function getCreditcardEditUrl($ccSaveID)
	{
		return $this->getUrl('authorizenetcim/index/ccedit', array('id'=>$ccSaveID));
	}
	/**
	 * Gets the URL to create a new credit card
	 */
	public function getCreditCardNewUrl()
	{
		return $this->getUrl('authorizenetcim/index/ccnew');
	}
	/**
	 * Delete URL for the cc
	 * @param int $ccSaveID
	 * @return Ambigous <string, mixed>
	 */
	public function getCreditcardDeleteUrl($ccSaveID)
	{
		return $this->getUrl('authorizenetcim/index/ccdelete', array('id'=> $ccSaveID));
	}
	/**
	 * Gets all the credit cards
	 * @param int $customerID
	 * @return multitype:multitype:string unknown
	 */
	public function getCreditcards($customerID, $storeId)
	{
		$savedCreditCards = Mage::getModel('authorizenetcim/authorizenetcim')
			->getCollection()
			->addFieldToFilter('customer_id', $customerID)
			->addFieldToFilter('store_id', $storeId)
			;
	
		$ccSaved = array();
	
		foreach ($savedCreditCards as $savedCreditCard)
		{
			$ccSaveId = $savedCreditCard->getTinybrickAuthorizenetcimCcsaveId();
			$ccCustomerId = $savedCreditCard->getCustomerId();
			$ccType = $savedCreditCard->getCcType();
			$ccLast4 = $savedCreditCard->getCcLast4();
			$ccExpMonth = $savedCreditCard->getCcExpMonth();
			$ccExpYear = $savedCreditCard->getCcExpYear();
				
			switch($ccType) {
				case 'AE':
					$ccFullLast4 = '31111111111'.$ccLast4;
					break;
				case 'VI':
					$ccFullLast4 = '411111111111'.$ccLast4;
					break;
				case 'MC':
					$ccFullLast4 = '511111111111'.$ccLast4;
					break;
				case 'DI':
					$ccFullLast4 = '611111111111'.$ccLast4;
					break;
			}
				
			$ccSaved[] = array(
					'id' => $ccSaveId,
					'customerid' => $ccCustomerId,
					'type' => $ccType,
					'last4' => $ccLast4,
					'expmonth' => $ccExpMonth,
					'expyear' => $ccExpYear,
					'fullcc' => $ccFullLast4,
			);
		}
		return $ccSaved;
	}
	
}