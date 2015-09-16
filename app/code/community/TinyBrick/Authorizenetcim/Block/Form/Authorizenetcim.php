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
class TinyBrick_Authorizenetcim_Block_Form_Authorizenetcim extends Mage_Payment_Block_Form_Cc
{
	/**
	 * @see Mage_Payment_Block_Form_Cc::_construct()
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('authorizenetcim/payment/form/cc.phtml');
	}	
	/**
	 * Gets all the saved credit cards from the database
	 * @param int $customerID
	 * @return multitype:multitype:number unknown
	 */
	public function getSavedCreditCards($customerID, $storeId)
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
			$ccType = $savedCreditCard->getCcType();
			$ccLast4 = $savedCreditCard->getCcLast4();
			$ccExpMonth = (int)$savedCreditCard->getCcExpMonth();
			$ccExpYear = $savedCreditCard->getCcExpYear();
			
			switch($ccType) {
				case 'AE':
					$ccFullLast4 = '37000000000'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
				case 'VI':
					$ccFullLast4 = '411111111111'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
				case 'MC':
					$ccFullLast4 = '555555555555'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
				case 'DI':
					$ccFullLast4 = '601100000000'.$ccLast4;
					$ccFullLast4 = (int)$ccFullLast4;
					break;
			}
			
			$ccSaved[] = array(
				'id' => $ccSaveId,
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