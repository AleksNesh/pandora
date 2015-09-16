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
class TinyBrick_Authorizenetcim_IndexController extends Mage_Core_Controller_Front_Action
{
	
	public function testAction()
	{
		$username = '6Nr86qLc';

		
	}
	
	public function indexAction(){
		
		// This will render the layout for the authorizenetcim module
		$this->loadLayout();
		$this->renderLayout();
		
	}
	
	public function preDispatch()
	{
		parent::preDispatch();
		$action = $this->getRequest()->getActionName();
		$loginUrl = Mage::helper('customer')->getLoginUrl();
		
		if(!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)){
			$this->setFlag('', self::FLAG_NO_DISPATCH, true);
		}
	}
	
	public function cceditAction()
	{
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
		if ($navigationBlock) {
			$navigationBlock->setActive('authorizenetcim/index/index');
		}
		$this->renderLayout();
	}
	
	public function ccnewAction()
	{
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
		if ($navigationBlock) {
			$navigationBlock->setActive('authorizenetcim/index/index');
		}
		$this->renderLayout();
	}
	
	/**
	 * Deletes the credit card on file
	 */
	
	public function ccdeleteAction()
	{
		$params = $this->getRequest()->getParams();
		
		$ccId = $params['id'];
		
		$profileData = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccId)->getData();
		$tokenProfileId = $profileData['token_profile_id'];
		$tokenPaymentProfileId = $profileData['token_payment_profile_id'];
		$customerId = $profileData['customer_id'];
		
		$profile = Mage::getModel('authorizenetcim/authorizenetcim');
		$profileCollection = $profile->getCollection()->addFieldToFilter('customer_id', $customerId);
		
		if (count($profileCollection) == 1) {
			
			$xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml');
		    $xml->deleteCustomerProfileRequest(array(
		        'customerProfileId' => $tokenProfileId
		    ));		
			
		}
		else {
			
		$xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml');
		$xml->deleteCustomerPaymentProfileRequest(array(
		        'customerProfileId' => $tokenProfileId,
		        'customerPaymentProfileId' => $tokenPaymentProfileId
		    ));
		
		}

		$profileUpload = Mage::getModel('authorizenetcim/authorizenetcim');
		$profileUpload->setId($ccId)->delete();			
		
		$this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
		
		return;		
	}
	/**
	 * submits the new credit card to put on file
	 */
	public function submitAction(){
				
		// This grabs the submitted parameters from the form
		$params = $this->getRequest()->getParams();
		
		
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$customerId = $customer->getId();
		$customerEmail = $customer->getEmail();

		$ccInfo = $params['payment'];
		if(isset($params['id'])){
			$ccId = $params['id'];
		}else{
			$ccId = $ccInfo['cc_cid'];
		}
		$ccType = $ccInfo['cc_type'];
		$ccNumber = $ccInfo['cc_number'];
		$ccExp = $ccInfo['cc_exp_year'] .'-'. str_pad($ccInfo['cc_exp_month'], 2, '0', STR_PAD_LEFT);
		$ccVer = $ccInfo['cc_cid'];
		
		$firstName = $params['firstname'];
		$lastName = $params['lastname'];
		$companyName = $params['company'];
		$phoneNumber = $params['telephone'];
		$faxNumber = $params['fax'];
		$streetArray = $params['street'];
		$city = $params['city'];
		$regionId = $params['region_id'];
		$zipCode = $params['postcode'];
		$countryId = $params['country_id'];
		
		// this creates the billing info if needed
		$billingInfo = array(
				'firstname' => $firstName,
				'lastname' => $lastName,
				'street' => $streetArray[0],
				'city' => $city,
				'region' => $regionId,
				'postcode' => $zipCode,
				'telephone' => $phoneNumber
				);
		
		$shippingInfo = array(
				'firstname' => $firstName,
				'lastname' => $lastName,
				'street' => $streetArray[0],
				'city' => $city,
				'region' => $regionId,
				'postcode' => $zipCode,
				'telephone' => $phoneNumber
		);
		
		$region = Mage::getModel('directory/region')->load($regionId);
		$stateCode =  $region->getCode();
		
		// First we need to check if payment type even exists
		
		// This checks to see if it exists 
		// If it does not exist, it needs to create it
		$profileData = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccId)->getData();
		if(!$params['edit'] && strlen($ccNumber) >= 10){
			if($profileData != null){
				$tokenProfileId = $profileData['token_profile_id'];
				$tokenPaymentProfileId = $profileData['token_payment_profile_id'];		
				
				$xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml');
				$xml->updateCustomerPaymentProfileRequest(array(
						        'customerProfileId' => $tokenProfileId,
						        'paymentProfile' => array(
						            'billTo' => array(
						                'firstName' => $firstName,
						                'lastName' => $lastName,
						                'company' => $companyName,
						                'address' => $streetArray[0],
						                'city' => $city,
						                'state' => $stateCode,
						                'zip' => $zipCode,
						                'country' => $countryId,
						                'phoneNumber' => $phoneNumber,
						                'faxNumber' => $faxNumber
						            ),
						            'payment' => array(
						                'creditCard' => array(
						                    'cardNumber' => $ccInfo[cc_number],
						                    'expirationDate' => $ccExp,
						                	'cardCode' => $ccVer
						                )
						            ),
						            'customerPaymentProfileId' => $tokenPaymentProfileId
						        )
						    ));
			}else{
				
				// gets the profile id
				$profile = Mage::getModel('authorizenetcim/authorizenetcim');
				$profile->load($customerId,'customer_id');
				$tokenProfileId = $profile->getTokenProfileId();
						
				
				//create the data
				$model = Mage::getModel('authorizenetcim/authorizenetcimsoap');
				
				
				if($tokenProfileId == null){
					// this needs to create the user
					$model->createCustomerProfileRequest($customerId, $customerEmail, $billingInfo, $shippingInfo, $ccNumber, $ccExp, $ccId, $ccType, $ccVer);
				}else{						
					// Create payment profile data
					$model->createCustomerPaymentProfileRequest($customerId, $tokenProfileId, $billingInfo, $ccNumber, $ccExp, $ccId, $ccType, $ccVer);
				}
			}
			
			$profileUpload = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccId);
			$profileUpload->setCcType($ccType)
				->setCcLast4(substr($ccNumber, -4, 4))
				->setCcExpMonth(substr($ccExp, -2))
				->setCcExpYear(substr($ccExp, 0, -3))
				->setStoreId(Mage::app()->getStore()->getStoreId())
				->save();
			
		}elseif($params['edit'] == 'edit' && strlen($ccNumber) >= 10){			
			if($profileData != null){
				$tokenProfileId = $profileData['token_profile_id'];
				$tokenPaymentProfileId = $profileData['token_payment_profile_id'];
			
				$xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml');
				$xml->updateCustomerPaymentProfileRequest(array(
						'customerProfileId' => $tokenProfileId,
						'paymentProfile' => array(
								'billTo' => array(
										'firstName' => $firstName,
										'lastName' => $lastName,
										'company' => $companyName,
										'address' => $streetArray[0],
										'city' => $city,
										'state' => $stateCode,
										'zip' => $zipCode,
										'country' => $countryId,
										'phoneNumber' => $phoneNumber,
										'faxNumber' => $faxNumber
								),
								'payment' => array(
										'creditCard' => array(
												'cardNumber' => $ccInfo[cc_number],
												'expirationDate' => $ccExp,
												'cardCode' => $ccVer
										)
								),
								'customerPaymentProfileId' => $tokenPaymentProfileId
						)
				));
			}else{
			
				// gets the profile id
				$profile = Mage::getModel('authorizenetcim/authorizenetcim');
				$profile->load($customerId,'customer_id');
				$tokenProfileId = $profile->getTokenProfileId();
			
				
				//create the data
				$model = Mage::getModel('authorizenetcim/authorizenetcimsoap');
			
			
				if($tokenProfileId == null){
					// this needs to create the user
					$model->createCustomerProfileRequest($customerId, $customerEmail, $billingInfo, $shippingInfo, $ccNumber, $ccExp, $ccId, $ccType, $ccVer);
				}else{
					// Create payment profile data
					$model->createCustomerPaymentProfileRequest($customerId, $tokenProfileId, $billingInfo, $ccNumber, $ccExp, $ccId, $ccType, $ccVer);
				}
			}
			
			$profileUpload = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccId);
			$profileUpload->setCcType($ccType)
				->setCcLast4(substr($ccNumber, -4, 4))
				->setCcExpMonth(substr($ccExp, -2))
				->setCcExpYear(substr($ccExp, 0, -3))
				->setStoreId(Mage::app()->getStore()->getStoreId())
				->save();
			
		}else{
			
			// gets the profile id
			$profile = Mage::getModel('authorizenetcim/authorizenetcim');
			$profile->load($customerId,'customer_id');
			$tokenProfileId = $profile->getTokenProfileId();
			
			$xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml');
			$xml->updateCustomerPaymentProfileRequest(array(
					'customerProfileId' => $tokenProfileId,
					'paymentProfile' => array(
							'billTo' => array(
									'firstName' => $firstName,
									'lastName' => $lastName,
									'company' => $companyName,
									'address' => $streetArray[0],
									'city' => $city,
									'state' => $stateCode,
									'zip' => $zipCode,
									'country' => $countryId,
									'phoneNumber' => $phoneNumber,
									'faxNumber' => $faxNumber
							),
							'payment' => array(
									'creditCard' => array(
											'cardNumber' => 'XXXX'.$ccNumber,
											'expirationDate' => $ccExp,
											'cardCode' => $ccVer
									)
							),
							'customerPaymentProfileId' => $profileData['token_payment_profile_id']
					)
			));
			
			Mage::helper('authorizenetcim')->response($xml);
			
			$profileUpload = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccId);
			$profileUpload->setCcType($ccType)
				->setCcExpMonth(substr($ccExp, -2))
				->setCcExpYear(substr($ccExp, 0, -3))
				->setVerification($ccVer)
				->setStoreId(Mage::app()->getStore()->getStoreId())
				->save();
		}
		$this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
		
		return;
		
		$test = Mage::getModel('creditcardsave/ccSave.php')->getParams(1);
		
		$this->_redirect('authorizenetcim');
	}
	
}