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
class TinyBrick_OrderEdit_Helper_Data extends Mage_Core_Helper_Abstract
{
        
	const MAXIMUM_AVAILABLE_NUMBER = 99999999;
	/**
         * Checks the quote to see if the items allowed are greater than the checkout qty
         * @param Mage_Sales_Model_Order $order Order Object
         * @param type $amount
         * @return TinyBrick_OrderEdit_Helper_Data 
         */
	public function checkQuoteAmount(Mage_Sales_Model_Order $order, $amount)
	{
		if (!$order->getHasError() && ($amount>=self::MAXIMUM_AVAILABLE_NUMBER)) {
			$order->setHasError(true);
			$order->addMessage(
		    $this->__('Some items have quantities exceeding allowed quantities. Please select a lower quantity to checkout.')
		);
	}
		return $this;
	}
        /**
         * Checks to see if the module is registered to the site
         * @return boolean
         */
        public function _isRegistered(){

                    $baseUrl = Mage::getBaseUrl();
                    if(preg_match('/127.0.0.1|localhost|192.168/', $baseUrl)){
                            return true;
                    }
                    if($registeredDomain = Mage::getStoreConfig('toe/oej/nfg')){
                            if(preg_match("/$registeredDomain/", $baseUrl)){
                                    if(($serial = Mage::getStoreConfig('toe/oej/wdf')) && $key = Mage::getStoreConfig('toe/oej/ntr')){
                                            if(md5($registeredDomain.$serial) == $key){
                                                    return true;
                                            } 
                                    }
                            }
                    }
                    return false;

        }
        
}

