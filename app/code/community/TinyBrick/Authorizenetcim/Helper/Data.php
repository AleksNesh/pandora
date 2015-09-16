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
class TinyBrick_Authorizenetcim_Helper_Data extends Mage_Core_Helper_Abstract
{

	/**
	 * Checks the response for errors. if found, throws an exception
	 * @param string $response
	 */
	public function response($response)
	{
		if(!$response->isSuccessful()){
			$result = $response->messages->resultCode;
			$resultCode = $response->messages->message->code;
			$resultText = $response->messages->message->text;
		
			Mage::throwException('Result: '.$result.' Code: '.$resultCode.' Message: '.$resultText);
		}
		else if($response->messages->resultCode != 'Ok'){
			$errorCode = $response->transactionResponse->errors->error->errorCode;
			$errorText = $response->transactionResponse->errors->error->errorText;
		
			Mage::throwException('Error Code: '.$errorCode.' Error Text: '.$errorText);
		}
		if(!empty($response->transactionResponse->errors->error->errorCode) && $response->transactionResponse->errors->error->errorCode > 1){
			$errorCode = $response->transactionResponse->errors->error->errorCode;
			$errorText = $response->transactionResponse->errors->error->errorText;
		
			Mage::throwException('Error Code: '.$errorCode.' Error Text: '.$errorText);
		}
	}
	/**
	 * Checks the response for errors. if found, throws an exception. 
	 * @param string $response
	 */
	public function result($response)
	{
		if(!$response->isSuccessful()){
			$result = $response->messages->resultCode;
			$resultCode = $response->messages->message->code;
			$resultText = $response->messages->message->text;
		
			Mage::throwException('Result: '.$result.' Code: '.$resultCode.' Message: '.$resultText);
		}
		else if($response->messages->resultCode != 'Ok'){
			$errorCode = $response->transactionResponse->errors->error->errorCode;
			$errorText = $response->transactionResponse->errors->error->errorText;
		
			Mage::throwException('Error Code: '.$errorCode.' Error Text: '.$errorText);
		}
		if(!empty($response->transactionResponse->errors->error->errorCode) && $response->transactionResponse->errors->error->errorCode > 1){
			$errorCode = $response->transactionResponse->errors->error->errorCode;
			$errorText = $response->transactionResponse->errors->error->errorText;
		
			Mage::throwException('Error Code: '.$errorCode.' Error Text: '.$errorText);
		}
	}
	
	public function billingResponse($response){
		$billing = array(
				'address' => (string) $response->paymentProfile->billTo->address,
				'city' => (string) $response->paymentProfile->billTo->city,
				'state' => (string) $response->paymentProfile->billTo->state,
				'zip' => (string) $response->paymentProfile->billTo->zip
				);
		return $billing;
	}
	
	public function getResult($response){
		if(!$response->isSuccessful()){
			$result = (string) $response->messages->resultCode;
			return false;	
		}else{
			return true;
		}	
	}
	
	public function isEnabled(){
		$baseUrl = Mage::getBaseUrl();
		if(preg_match('/127.0.0.1|localhost|192.168/', $baseUrl)){
			return true;
		}
		if($registeredDomain = Mage::getStoreConfig('payment/authorizenetcim/nfg')){
			if(preg_match("/$registeredDomain/", $baseUrl)){
				if(($serial = Mage::getStoreConfig('payment/authorizenetcim/wdf')) && $key = Mage::getStoreConfig('payment/authorizenetcim/ntr')){
					if(md5($registeredDomain.$serial) == $key){
						return true;
					}
				}
			}
		}
		return false;		
	}
	
	public function getAllMatchingStores($username)
	{
            
            $stores = Mage::app()->getStores();
            $storeIds = array();
            
            foreach($stores as $store){
			$test =  Mage::getStoreConfig('payment/authorizenetcim/test_mode', $store->getStoreId());
                if($test == 1){
                    $allUsername = Mage::getStoreConfig('payment/authorizenetcim/test_username', $store->getStoreId());
                }else{
                    $allUsername = Mage::getStoreConfig('payment/authorizenetcim/username', $store->getStoreId());
                }
                if($username == $allUsername){
                        $storeIds[] = $store->getStoreId();
                }
            }
            return $storeIds;
	}
	
	public function saveToDatabase($customerID, $ccType, $ccNumber, $ccExpDate, $customerProfileID, $customerPaymentProfileID, $customerShippingAddressID, $storeId)
	{
		$profileUpload = Mage::getModel('authorizenetcim/authorizenetcim');
		$profileUpload->setCustomerID($customerID)
			->setCcType($ccType)
			->setCcLast4(substr($ccNumber, -4, 4))
			->setCcExpMonth(substr($ccExpDate, -2))
			->setCcExpYear(substr($ccExpDate, 0, -3))
			->setTokenProfileId($customerProfileID)
			->setTokenPaymentProfileId($customerPaymentProfileID)
			->setTokenShippingAddressId($customerShippingAddressID)
			->setStoreId($storeId)
			->save();
	}
}
