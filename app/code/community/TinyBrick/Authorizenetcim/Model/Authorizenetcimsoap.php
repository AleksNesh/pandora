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
class TinyBrick_Authorizenetcim_Model_Authorizenetcimsoap extends Mage_Payment_Model_Method_Cc
{
	
	protected $_code = 'authorizenetcim';
	protected $_formBlockType = 'authorizenetcim/form_authorizenetcim';
	protected $_isGateway = true;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canVoid = true;
	protected $_canRefund = true;
	protected $_canUseCheckout = true;
	public $_storeId;
	public $_guest = 0;
    public $_admin = 0;
	
	/**
	 * This getPaymentAction() gets the payment type from Magento 
	 */
	
	public function getPaymentAction()
	{
		return Mage::getStoreConfig('payment/authorizenetcim/payment_action');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Payment_Model_Method_Abstract::authorize()
	 * This overwrites the authorize function and calls the callApi function
	 * From here, it contacts authorize.net
	 * Mage::helper('authorizenetcim')->response($response) - checks the response to make sure it is valid
	 */
	public function authorize(Varien_Object $payment, $amount)
	{
		$customer = $payment->getOrder()->getCustomerId();
		$this->_storeId = $payment->getOrder()->getStoreId();
		
		$response = $this->callApi($payment, $amount, 'authorize');
		
		// Checks to see if we can connect to Authorize.net
		Mage::helper('authorizenetcim')->response($response);
		
		$directResponseFields = explode(",", $response->directResponse);
		$transactionId = $directResponseFields[6];
		
		$payment->setTransactionId($transactionId);
		$payment->setIsTransactionClosed(0);
		$payment->setCcTransId($transactionId);
		
		$orderId = $payment->getOrder()->getIncrementId();
		
		/**
		 * If TEO is installed, it will run this
		 * TODO add in check if module is installed
		 */
		$this->teoAuth($orderId, $transactionId, $type = 'authorize', $amount);
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Payment_Model_Method_Abstract::capture()
	 * Overwrites the capture function and calls the callApi function
	 * Contacts authorize.net
	 * Mage::helper('authorizenetcim')->response($response) - checks the response to make sure it is valid
	 * @param object $payment Payment object
	 * @param int $amount Amount to capture
	 * @param string $type This is either useCIM or useAIM 
	 */
	
	public function capture(Varien_Object $payment, $amount, $type = NULL)
	{
		$order = $payment->getOrder();
		$this->_storeId = $order->getStoreId();
                
                Mage::getSingleton('core/session', array('name'=>'adminhtml'));
                if(Mage::getSingleton('admin/session')->isLoggedIn()){
                    $this->_admin = 1;
                }
		
		if($this->getPaymentAction()=='authorize') {
			if ($type == NULL || $type == 'useCIM'){
				$response = $this->callApi($payment, $amount, 'capture');
				
				// Checks to see if we can connect to Authorize.net
				Mage::helper('authorizenetcim')->response($response);
				
				$directResponseFields = explode(",", $response->directResponse);
				$transactionId = $directResponseFields[6];
			}elseif($type == 'useAIM'){
				$response = $this->callApi($payment, $amount, 'captureAIM');
				
				// Checks to see if we can connect to Authorize.net
				Mage::helper('authorizenetcim')->response($response);
				
				$transactionId = $response->transactionResponse->transId;
			}
		}
		else {
			if ($type == NULL || $type == 'useCIM'){
				$response = $this->callApi($payment, $amount, 'authorizeandcapture');
				
				// Checks to see if we can connect to Authorize.net
				Mage::helper('authorizenetcim')->response($response);
				
				$directResponseFields = explode(",", $response->directResponse);
				$transactionId = $directResponseFields[6];
			}
			else if ($type == 'useAIM'){
				$response = $this->callApi($payment, $amount, 'authorizeandcaptureAIM');
				
				// Checks to see if we can connect to Authorize.net
				Mage::helper('authorizenetcim')->response($response);
				
				$transactionId = $response->transactionResponse->transId;
			}
		}
				
		$payment->setCcTransId($transactionId);  // probably shouldn't set.
		$payment->setTransactionId($transactionId);
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Payment_Model_Method_Abstract::void()
	 * Voids the transaction
	 * @param object $payment Payment object to void
	 */
	
	public function void(Varien_Object $payment)
	{
		
		$order = $payment->getOrder();
		$this->_storeId = $order->getStoreId();
		
		$response = $this->callApi($payment, NULL ,'void');
		
		Mage::helper('authorizenetcim')->result($response);
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Payment_Model_Method_Abstract::refund()
	 * Refunds the payment based on the object/amount
	 * @param object $payment Payment object
	 * @param int $amount Total to void
	 */
	
	public function refund(Varien_Object $payment, $amount) {
		
		$order = $payment->getOrder();
		$this->_storeId = $order->getStoreId();
		
		$tokenPaymentProfileId = $payment->getTokenPaymentProfileId();
		
		if($tokenPaymentProfileId != 0){
			$response = $this->callApi($payment, $amount, 'refund');
			
			Mage::helper('authorizenetcim')->result($response);
		
		}
		else{
			$response = $this->callApi($payment, $amount, 'refundAIM');
			
			Mage::helper('authorizenetcim')->result($response);
		
		}
		
		return $this;
	}
	
	/**
	 * callApi is the major piece in the puzzle
	 * @param object $payment Payment Object
	 * @param int $amount Amount to charge
	 * @param string $type either CIM or AIM
	 * @param int $ccSaveId Used to determine whether or not a profile exists for the customer
	 * @param int $tokenProfileId Checks if the payment profile already exists, if not, creates it
	 */
	
	//prepare information and call specific xml api
	public function callApi(Varien_Object $payment, $amount, $type){
	
		$order = $payment->getOrder();
		$invoiceNumber = $order->getIncrementId();
		if($type != 'authorizeandcaptureAIM'){
			/**
			 * This will build the profile for the customer
			 */
			$customerID = $order->getCustomerId();
			$customerEmail = $order->getCustomerEmail();
			if(!$customerID){
				/**
				 * This will build the guest customer ID since they do not exist in the database yet
				*/
				$this->_guest = 1;
				//$customerEmail = 'guest-'. $customerEmail = $order->getCustomerEmail();
				$guestCheck = Mage::getModel('authorizenetcim/guests')->load($customerEmail, 'email');
				
				if(!$guestCheck->getData()){
					$guest = Mage::getModel('authorizenetcim/guests');
					$guest->setEmail($customerEmail);
					$guest->save();
					$customerID = $guest->getGuestId();
				}else{
					$customerID = $guestCheck->getGuestId();
				}
			}
			
			$billingInfo = $order->getBillingAddress();
			$shippingInfo = $order->getShippingAddress();
			
			$ccType = $payment->getCcType();
			$ccNumber = $payment->getCcNumber();
			$ccExpDate = $payment->getCcExpYear() .'-'. str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT);
			$ccCCV = $payment->getCcCid();
			/**
			 * Checks to see if there is a token for profile and payment already associated with the customer
			 * If it is a guest, there will not be one
			 * I believe this may be extra unncessary code
			 */
			$tokenProfileId = $payment->getTokenProfileId();
			$tokenPaymentProfileId = $payment->getTokenPaymentProfileId();
			
			$postData = Mage::app()->getRequest()->getPost('payment', array());
			if(isset($postData['ccsave_id'])){
				$ccSaveId = $postData['ccsave_id'];
			}
			
			if($customerID == $order->getIncrementId())
			{
				/**
				 * Can combine it with the below code to make 
				 * it an || statement instead of having the extra 
				 * lines of code
				 */
				$profileData = Mage::getModel('authorizenetcim/authorizenetcim')
									->load($customerID, 'customer_id')->getData();
				$tokenProfileId = $profileData['token_profile_id'];
				$tokenPaymentProfileId = $profileData['token_payment_profile_id'];
			}elseif(isset($ccSaveId)){
				$profileData = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccSaveId)->getData();
				$tokenProfileId = $profileData['token_profile_id'];
				$tokenPaymentProfileId = $profileData['token_payment_profile_id'];
			}
			
			if(($tokenProfileId==0 && $tokenPaymentProfileId==0) && ($type == 'authorize' 
					|| $type == 'capture' || $type == 'authorizeandcapture')){
				/**
				 * If token doesn't exist and type is = authorize, capture or authorizeandcapture
				 * then it will create the token for you through authorize.net
				 * and save it to the database
				 */
				if(isset($ccSaveId)){
					/** 
					 * Can most likely be removed since it was done above already
					 * Ambiguous call to the database 
					 */
					$profileData = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccSaveId)->getData();
					$tokenProfileId = $profileData['token_profile_id'];
					$tokenPaymentProfileId = $profileData['token_payment_profile_id'];
				}else{
					
					$profile = Mage::getModel('authorizenetcim/authorizenetcim');
					$profileCollection = $profile->getCollection()
							->addFieldToFilter('customer_id', $customerID)
							->addFieldToFilter('store_id', Mage::app()->getStore()->getStoreId())
							;
					
					if (count($profileCollection) == 0) {
						/**
						 * If customer doesn't already exist in our database, it will try 
						 * to create it through the authorize.net section. It will also create the first initial 
						 * payment profile id
						 */
						$responseXML = $this->createCustomerProfileRequest($customerID, $customerEmail, 
								$billingInfo, $shippingInfo, $ccNumber, $ccExpDate, $ccCCV, $ccType, $this->_guest);
						
						$tokenProfileId = $responseXML->customerProfileId;
						$tokenPaymentProfileId = $responseXML->customerPaymentProfileIdList->numericString;
					}else{
						/**
						 * If customer already exists, it will get the profileID and then create the new
						 * customer payment profile ID
						 */
						$tokenProfileId = $profileCollection->getFirstItem()->getTokenProfileId();
						/**
						 * Before we create a new payment profile id, we need to check
						 * and see if it already exists
						 */
						// gets the last 4 of the cc
						$ccLast4 = substr($ccNumber, -4, 4);
						$tokenCheck = Mage::getModel('authorizenetcim/authorizenetcim')
							->getCollection()
							->addFieldToFilter('token_profile_id',$tokenProfileId)
							->addFieldToFilter('cc_last4', $ccLast4)
							->addFieldToSelect('token_payment_profile_id')
                                                        ->addFieldToSelect('token_profile_id')
							;
						$token = $tokenCheck->getFirstItem()->getData();
						if(empty($token)){
							$tokenPaymentProfileId = $this->createCustomerPaymentProfileRequest($customerID, 
									$tokenProfileId, $billingInfo, $ccNumber, $ccExpDate, $ccCCV, $ccType);
						}else{
                                                    $tokenProfileId = $token['token_profile_id'];
                                                    $tokenPaymentProfileId = $token['token_payment_profile_id'];
                                                }
					}
					
				}
			}
		}
		//call xml creation functions
		switch($type) {
			case 'authorize':
				$response = $this->runAuthorize($payment, $customerID, $amount, (string)$tokenProfileId, (string)$tokenPaymentProfileId, 
						$invoiceNumber, $ccCCV);
				break;
			case 'capture':
				$response = $this->runCapture($payment, $invoiceNumber, $amount, $tokenProfileId, $tokenPaymentProfileId);
				break;
			case 'authorizeandcapture':
				$response = $this->runAuthorizeAndCapture($payment, $amount, $tokenProfileId, $tokenPaymentProfileId,
					$invoiceNumber, $ccCCV);
				break;
			case 'void':
				$response = $this->runVoid($payment, $tokenProfileId, $tokenPaymentProfileId, $refundTransactionId);
				break;
			case 'refund':
				$response = $this->runRefund($payment, $invoiceNumber, $amount, $tokenProfileId, $tokenPaymentProfileId);
				break;
			case 'authorizeandcaptureAIM':
				$response = $this->createAuthorizeCaptureAIM($amount, $payment, $order);
				break;
			case 'captureAIM':
				$response = $this->captureAIM($tokenProfileId);
				break;
			case 'createauthorizeaim':
				$response = $this->createAuthorizeAim($amount, $payment, $order);
				break;
			case 'refundAIM':
				$response = $this->createRefundAIM($amount, $payment, $order);
				break;
		}

		return $response;
	}	
	
	/**
	 * This sends the data to authorize.net and creates the customer profile request
	 * 
	 * It then saves the data to the database
	 * 
	 * @param int $customerID
	 * @param string $customerEmail
	 * @param object $billingInfo
	 * @param object $shippingInfo
	 * @param int $ccNumber
	 * @param date $ccExpDate
	 * @param int $ccCCV
	 * @param string $ccType
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createCustomerProfileRequest($customerID, $customerEmail, $billingInfo, $shippingInfo, 
			$ccNumber, $ccExpDate, $ccCCV, $ccType, $guest)
	{
		$xml = $this->getAuthModel();
		$xml->createCustomerProfileRequest(array(
				'profile' => array(
						'merchantCustomerId' => $customerID,
						'email' => $customerEmail,
						'paymentProfiles' => array(
								'billTo' => array(
										'firstName' => $billingInfo['firstname'],
										'lastName' => $billingInfo['lastname'],
										'address' => $billingInfo['street'],
										'city' => $billingInfo['city'],
										'state' => $billingInfo['region'],
										'zip' => $billingInfo['postcode'],
										'phoneNumber' => $billingInfo['telephone']
								),
								'payment' => array(
										'creditCard' => array(
												'cardNumber' => $ccNumber,
												'expirationDate' => $ccExpDate,
										),
								),
						),
						'shipToList' => array(
								'firstName' => $billingInfo['firstname'],
								'lastName' => $billingInfo['lastname'],
								'address' => $billingInfo['street'],
								'city' => $billingInfo['city'],
								'state' => $billingInfo['region'],
								'zip' => $billingInfo['postcode'],
								'phoneNumber' => $billingInfo['telephone']
						),
				),
				'validationMode' => 'none'
		));	
		
		Mage::helper('authorizenetcim')->result($xml);

		$customerProfileID = $xml->customerProfileId;
		$customerPaymentProfileID = $xml->customerPaymentProfileIdList->numericString;
		$customerShippingAddressID = $xml->customerShippingAddressIdList->numericString;
		
		$username = $xml->getUsername();
		
		if($customerID != 0){			
			/**
			 * This will check if there are multiple stores with the same exact username, if so
			 * it will loop through the stores and create the MySQL insert or the 
			 * multiple store ID's
			 */
			$storeIds = Mage::helper('authorizenetcim')->getAllMatchingStores($username);
			foreach($storeIds as $store){
				Mage::helper('authorizenetcim')->saveToDatabase($customerID, $ccType, $ccNumber, $ccExpDate, $customerProfileID, 
						$customerPaymentProfileID, $customerShippingAddressID, $store);
			}
		}
				
		return $xml;
	}

	
	/**
	 * 
	 * Creates the customer profile inside of authorize.net
	 * 
	 * @param int $customerID
	 * @param int $tokenProfileId
	 * @param object $billingInfo
	 * @param int $ccNumber
	 * @param date $ccExpDate
	 * @param int $ccCCV
	 * @param string $ccType
	 * @return int $customerPaymentProfileID
	 */
	
	public function createCustomerPaymentProfileRequest($customerID, $tokenProfileId, $billingInfo, $ccNumber, 
			$ccExpDate, $ccCCV, $ccType)
	{
		$xml = $this->getAuthModel();
		$xml->createCustomerPaymentProfileRequest(array(
				'customerProfileId' => $tokenProfileId,
				'paymentProfile' => array(
						'billTo' => array(
								'firstName' => $billingInfo['firstname'],
								'lastName' => $billingInfo['lastname'],
								'address' => $billingInfo['street'],
								'city' => $billingInfo['city'],
								'state' => $billingInfo['region'],
								'zip' => $billingInfo['postcode'],
								'phoneNumber' => $billingInfo['telephone']
						),
						'payment' => array(
								'creditCard' => array(
										'cardNumber' => $ccNumber,
										'expirationDate' => $ccExpDate,
								)
						)
				),
				'validationMode' => 'none'
		));		
		
		Mage::helper('authorizenetcim')->result($xml);

		$customerProfileID = $tokenProfileId;
		$customerPaymentProfileID = $xml->customerPaymentProfileId;
		$username = $xml->getUsername();
		
		if($customerID != 0){			
			/**
			 * This will check if there are multiple stores with the same exact username, if so
			 * it will loop through the stores and create the MySQL insert or the 
			 * multiple store ID's
			 */
			$storeIds = Mage::helper('authorizenetcim')->getAllMatchingStores($username);
			foreach($storeIds as $store){
				Mage::helper('authorizenetcim')->saveToDatabase($customerID, $ccType, $ccNumber, $ccExpDate, $customerProfileID, 
						$customerPaymentProfileID, $customerShippingAddressID, $store);
			}
		}
		
		return $customerPaymentProfileID;
		
	}
	
	/**
	 * Creates the authorization inside of authorize.net
	 * 
	 * @param int $amount Amount of the authorization
	 * @param int $tokenProfileId Customer profile Id
	 * @param int $tokenPaymentProfileId Customer Payment Profile ID to use
	 * @param int $invoiceNumber Invoice number from Magento
	 * @param int $ccCCV Last 4 of the Credit Card
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createAuthorize($amount, $tokenProfileId, $tokenPaymentProfileId, $invoiceNumber, $ccCCV)
	{
		$xml = $this->getAuthModel();
		$xml->createCustomerProfileTransactionRequest(array(
				'transaction' => array(
						'profileTransAuthOnly' => array(
								'amount' => $amount,
								'customerProfileId' => $tokenProfileId,
								'customerPaymentProfileId' => $tokenPaymentProfileId,
								'order' => array(
										'invoiceNumber' => $invoiceNumber,
								),
						)
				),
		));		
			
		return $xml;
	}
	
	/**
	 * Creates the capture in authorize.net
	 * 
	 * @param int $amount Amount of the authorization
	 * @param int $tokenProfileId Customer profile Id
	 * @param int $tokenPaymentProfileId Customer Payment Profile ID to use
	 * @param int $invoiceNumber Invoice number from Magento
	 * @param int $ccCCV Last 4 of the Credit Card
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createCapture($amount, $tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId)
	{
		// Check if you are in admin panel
                $xml = $this->getAuthModel();
		$xml->createCustomerProfileTransactionRequest(array(
				'transaction' => array(
						'profileTransPriorAuthCapture' => array(
								'amount' => $amount,
								'customerProfileId' => $tokenProfileId,
								'customerPaymentProfileId' => $tokenPaymentProfileId,
								'transId' => $authorizeTransactionId
						)
				),
		));		
		
		return $xml;
	}
	
	/**
	 * 
	 * Authorizes and then captures inside authorize.net
	 * 
	 * @param int $amount Amount of the authorization
	 * @param int $tokenProfileId Customer profile Id
	 * @param int $tokenPaymentProfileId Customer Payment Profile ID to use
	 * @param int $invoiceNumber Invoice number from Magento
	 * @param int $ccCCV Last 4 of the Credit Card
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createAuthorizeCapture($amount, $tokenProfileId, $tokenPaymentProfileId, $invoiceNumber, $ccCCV)
	{
		$xml = $this->getAuthModel();
		$xml->createCustomerProfileTransactionRequest(array(
				'transaction' => array(
						'profileTransAuthCapture' => array(
								'amount' => $amount,
								'customerProfileId' => $tokenProfileId,
								'customerPaymentProfileId' => $tokenPaymentProfileId,
								//'customerShippingAddressId' => '12156448',
								'order' => array(
										'invoiceNumber' => $invoiceNumber,
								),
								//may have to add if statement, if pulled from profile, do not pass CCV info; if credit card number entered for first time, pass CCV  (CCV is not stored in CIM table for use)
								//'cardCode' => $ccCCV
						)
				),
		));		
		
		return $xml;
		
	}
	
	/**
	 * Voides the transaction in authorize.net 
	 * 
	 * @param int $tokenProfileId Customer profile ID
	 * @param int $tokenPaymentProfileId Customer payment profile ID 
	 * @param int $authorizeTransactionId Authorize.net transaction ID to void
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createVoid($tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId)
	{
		$xml = $this->getAuthModel();
		$xml->createCustomerProfileTransactionRequest(array(
				'transaction' => array(
						'profileTransVoid' => array(
								'customerProfileId' => $tokenProfileId,
								'customerPaymentProfileId' => $tokenPaymentProfileId,
								//'customerShippingAddressId' => '4907537',
								'transId' => $authorizeTransactionId
						)
				),
		));
		return $xml;
	}
	
	/**
	 * 
	 * 
	 * @param int $amount Amount of the authorization
	 * @param int $tokenProfileId Customer profile ID
	 * @param int $tokenPaymentProfileId Customer payment profile ID 
	 * @param int $authorizeTransactionId Authorize.net transaction ID to void
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createRefund($amount, $tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId)
	{
		$xml = $this->getAuthModel();
		$xml->createCustomerProfileTransactionRequest(array(
				'transaction' => array(
						'profileTransRefund' => array(
								'amount' => $amount,
								'customerProfileId' => $tokenProfileId,
								'customerPaymentProfileId' => $tokenPaymentProfileId,
								'transId' => $authorizeTransactionId
						)
				),
		));
		
		return $xml;
	}
	
	/**
	 *  This uses AIM instead of CIM if the customer chooses not to save the card
	 * @param int $amount Amount to charge
	 * @param int $payment
	 * @param int $order Order Id
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createAuthorizeCaptureAIM($amount, $payment, $order)
	{
		$billingInfo = $order->getBillingAddress();
		$ccExpDate = $payment->getCcExpYear() .'-'. str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT);	
		
		$xml = $this->getAuthModel();
		$xml->createTransactionRequest(array(
		        'transactionRequest' => array(
		            'transactionType' => 'authCaptureTransaction',
		            'amount' => $amount,
		            'payment' => array(
		                'creditCard' => array(
		                    'cardNumber' => $payment->getCcNumber(),
		                    'expirationDate' => $ccExpDate,
		                ),
		            ),
		            'order' => array(
		                'invoiceNumber' => $order->getIncrementId(),
		            ),
		            'customer' => array(
		               'id' => $order->getCustomerId(),
		               'email' => $order->getCustomerEmail(),
		            ),
		            'billTo' => array(
		            		'firstName' => $billingInfo['firstname'],
		            		'lastName' => $billingInfo['lastname'],
		            		'address' => $billingInfo['street'],
		            		'city' => $billingInfo['city'],
		            		'state' => $billingInfo['region'],
		            		'zip' => $billingInfo['postcode'],
		            		'phoneNumber' => $billingInfo['telephone']
		            ),
		        ),
		    ));
		
		return $xml;
	
	}	
	
	public function captureAIM($auth)
	{
		$xml = $this->getAuthModel();
		$xml->createTransactionRequest(array(
				'transactionRequest' => array(
						'transactionType' => 'priorAuthCaptureTransaction',
						'refTransId' => $auth,
				),
		));
		
		Mage::helper('authorizenetcim')->result($xml);
		
		return $xml;
	}
	
	public function createAuthorizeAim($amount, $payment, $order)
	{
		$billingInfo = $order->getBillingAddress();
		$ccExpDate = $payment->getCcExpYear() .'-'. str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT);
		
		$xml = $this->getAuthModel();
		$xml->createTransactionRequest(array(
				'transactionRequest' => array(
						'transactionType' => 'authOnlyTransaction',
						'amount' => $amount,
						'payment' => array(
								'creditCard' => array(
										'cardNumber' => $payment->getCcNumber(),
										'expirationDate' => $ccExpDate,
								),
						),
						'order' => array(
								'invoiceNumber' => $order->getIncrementId(),
						),
						'customer' => array(
								'id' => $order->getCustomerId(),
								'email' => $order->getCustomerEmail(),
						),
						'billTo' => array(
								'firstName' => $billingInfo['firstname'],
								'lastName' => $billingInfo['lastname'],
								'address' => $billingInfo['street'],
								'city' => $billingInfo['city'],
								'state' => $billingInfo['region'],
								'zip' => $billingInfo['postcode'],
								'phoneNumber' => $billingInfo['telephone']
						),
				),
		));		
		
		Mage::helper('authorizenetcim')->result($xml);
		
		$invoiceNumber = $xml->invoiceNumber;
		$transactionKey = $xml->transactionResponse->transId;

		$profileUpload = Mage::getModel('authorizenetcim/authorizenetcim');
		$profileUpload->setCustomerID($order->getIncrementId())
			->setTokenProfileId((string)$transactionKey)
			->save();
				
		return $xml;
	}
	
	/**
	 * Voids the AIM transaction
	 * @param int $amount Amount to charge
	 * @param int $payment
	 * @param int $order Order Id
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createVoidAIM($amount, $payment, $order)
	{	
            $refundTransactionId = $payment->getRefundTransactionId();
	
            $xml = $this->getAuthModel();
	    $xml->createTransactionRequest(array(
	        'transactionRequest' => array(
	            'transactionType' => 'voidTransaction',
	            'refTransId' => $refundTransactionId
	        ),
	    ));
		
            return $xml;
	
	}
	
	/**
	 * Refunds the AIM transaction
	 * @param int $amount Amount to charge
	 * @param int $payment
	 * @param int $order Order Id
	 * @return Mage_Authorizenetcim_Model_Authorizenetcim_Authnetxml
	 */
	
	public function createRefundAIM($amount, $payment, $order)
	{
            $refundTransactionId = $payment->getRefundTransactionId();
            $ccLast4 = $payment->getCcLast4();
            $ccExpDate = str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT) . $payment->getCcExpYear();
	
            $xml = $this->getAuthModel();
	    $xml->createTransactionRequest(array(
	        'transactionRequest' => array(
	            'transactionType' => 'refundTransaction',
	            'amount' => $amount,
	            'payment' => array(
	                'creditCard' => array(
	                    'cardNumber' => 'XXXX'.$ccLast4,
	                	'expirationDate' => $ccExpDate
	                )
	            ),
	            'refTransId' => $refundTransactionId
	        ),
	    ));
		
            return $xml;
	
	}
	
	/**
	 * Not currently used
	 * (non-PHPdoc)
	 * @see Mage_Payment_Model_Method_Cc::validate()
	 */
	
	public function validate(){}
        
        public function getAuthModel()
        {
            $storeId = array();
            $storeId['store'] = $this->_storeId;
            return Mage::getModel('authorizenetcim/authorizenetcim_authnetxml', $storeId);
        }
        
        public function getCustomerProfileRequest($profileId)
        {
            $xml = $this->getAuthModel();
            $xml->getCustomerProfileRequest(array(
                        'customerProfileId' => $profileId
                    ));
            Mage::helper('authorizenetcim')->result($xml);
            return $xml;
        }
	
	public function getCustomerPaymentProfileRequest($profileId, $paymentId)
	{
		$xml = $this->getAuthModel();
		$xml->getCustomerPaymentProfileRequest(array(
				'customerProfileId' => $profileId,
				'customerPaymentProfileId' => $paymentId
				));
		$billing = Mage::helper('authorizenetcim')->billingResponse($xml);
		return $billing;
	}
	
	public function teoAuth($orderId, $transactionId, $type, $amount)
	{
		$teoAuths = Mage::getModel('authorizenetcim/teoauths');
		$teoAuths->setOrderId($orderId);
		$teoAuths->setAuthorizationNumber($transactionId);
		$teoAuths->setType('authorize');
		$teoAuths->setAuthorizationAmount($amount);
		/**
		 * @todo
		 * Need to setup storeId for this as well
		 */
		$teoAuths->save();
	}
	
	public function runAuthorize($payment, $customerID, $amount, $tokenProfileId, $tokenPaymentProfileId, $invoiceNumber, $ccCCV)
	{
		$payment->setTokenProfileId($tokenProfileId);
		$payment->setTokenPaymentProfileId($tokenPaymentProfileId);
		
		if($this->_guest == 1){
			/**
			 * Sets the guest == to 1 if it is a guest customer
			 */
			$payment->setGuestID($customerID);
		}
		
		$response = $this->createAuthorize($amount, $tokenProfileId, $tokenPaymentProfileId,
				$invoiceNumber, $ccCCV);
		return $response;
	}
	
	public function runCapture($payment, $invoiceNumber, $amount, $tokenProfileId, $tokenPaymentProfileId)
	{
		$teoAuths = Mage::getModel('authorizenetcim/teoauths');
		$authsCollection = $teoAuths->getCollection()->addFieldToFilter('order_id', $invoiceNumber);
		
		if (count($authsCollection) > 1) {
		
			$amountLeftToCapture = $amount;
		
			foreach ($authsCollection as $auths){
				$teoAuths->load($auths->getId());
				$teoAuthAmount = $teoAuths->getAuthorizationAmount();
				$teoAuthAmountPaid = $teoAuths->getAmountPaid();
		
				if ($amountLeftToCapture > 0){
		
					$amountLeftOnAuth = $teoAuthAmount - $teoAuthAmountPaid;
					$authorizeTransactionId = $teoAuths->getAuthorizationNumber();
		
					if($amountLeftToCapture > $amountLeftOnAuth) {
						$response = $this->createCapture($amountLeftOnAuth, $tokenProfileId,
								$tokenPaymentProfileId, $authorizeTransactionId);
		
						$teoAuths->setAmountPaid($amountLeftOnAuth);
						$teoAuths->save();
		
						$amountLeftToCapture = $amountLeftToCapture - $amountLeftOnAuth;
					}
					else {
						$response = $this->createCapture($amountLeftToCapture, $tokenProfileId,
								$tokenPaymentProfileId, $authorizeTransactionId);
		
						$teoAuths->setAmountPaid($amountLeftToCapture);
						$teoAuths->save();
		
						$amountLeftToCapture = 0;
					}
				}
			}
		}
		else{
			//get authorize transaction id for capture
			$authorizeTransactionId = $payment->getCcTransId();
			$response = $this->createCapture($amount, $tokenProfileId, $tokenPaymentProfileId,
					$authorizeTransactionId);
		}
		return $response;
	}
	
	public function runAuthorizeAndCapture($payment, $amount, $tokenProfileId, $tokenPaymentProfileId, $invoiceNumber, $ccCCV)
	{
		$payment->setTokenProfileId($tokenProfileId);
		$payment->setTokenPaymentProfileId($tokenPaymentProfileId);
		
		$response = $this->createAuthorizeCapture($amount, $tokenProfileId, $tokenPaymentProfileId,
				$invoiceNumber, $ccCCV);
		return $response;
	
	}
	
	public function runVoid($payment, $tokenProfileId, $tokenPaymentProfileId, $refundTransactionId)
	{
		$refundTransactionId = $payment->getRefundTransactionId();
		$response = $this->createVoid($tokenProfileId, $tokenPaymentProfileId, $refundTransactionId);
		return $response;
	}
	
	public function runRefund($payment, $invoiceNumber, $amount, $tokenProfileId, $tokenPaymentProfileId)
	{
		$teoAuths = Mage::getModel('authorizenetcim/teoauths');
		$authsCollection = $teoAuths->getCollection()->addFieldToFilter('order_id', $invoiceNumber);
		
		if (count($authsCollection) > 1) {
		
			$amountLeftToRefund = $amount;
		
			foreach ($authsCollection as $auths){
				$teoAuths->load($auths->getId());
				$teoAuthAmount = $teoAuths->getAuthorizationAmount();
				$teoAuthAmountRefunded = $teoAuths->getAmountRefunded();
		
				if ($amountLeftToRefund > 0){
		
					$amountLeftOnAuth = $teoAuthAmount - $teoAuthAmountRefunded;
					$authorizeTransactionId = $teoAuths->getAuthorizationNumber();
		
					if($amountLeftToRefund > $amountLeftOnAuth) {
						$response = $this->createRefund($amountLeftOnAuth, $tokenProfileId,
								$tokenPaymentProfileId, $authorizeTransactionId);
		
						$teoAuths->setAmountRefunded($amountLeftOnAuth);
						$teoAuths->save();
		
						$amountLeftToRefund = $amountLeftToRefund - $amountLeftOnAuth;
					}
					else {
						$response = $this->createRefund($amountLeftToRefund, $tokenProfileId,
								$tokenPaymentProfileId, $authorizeTransactionId);
		
						$teoAuths->setAmountRefunded($amountLeftToRefund);
						$teoAuths->save();
		
						$amountLeftToRefund = 0;
					}
				}
			}
		}
		else{
			$refundTransactionId = $payment->getRefundTransactionId();
			$response = $this->createRefund($amount, $tokenProfileId, $tokenPaymentProfileId,
					$refundTransactionId);
		}
		return $response;
	}
        
        public function getProfileData($ccId)
        {
            return Mage::getModel('authorizenetcim/authorizenetcim')->load($ccId)->getData();
        }
        
        public function getCustomerData($params)
        {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $ccId = (isset($params['id']) ? $params['id'] : $params['payment']['cc_cid']);
            if(strlen($params['payment']['cc_number']) == 4){
                $params['payment']['cc_number'] = 'XXXX'. $params['payment']['cc_number'];
            }
            $customerData = array(
                'customerId' => $customer->getId(),
                'customerEmail' => $customer->getEmail(),
                'ccInfo' => $params['payment'],
                'ccId'  => $ccId,
                'ccType' => $params['payment']['cc_type'],
                'ccNumber' => $params['payment']['cc_number'],
                'ccExp' => $params['payment']['cc_exp_year'] .'-'. str_pad($params['payment']['cc_exp_month'], 2, '0', STR_PAD_LEFT),
                'ccVer' => $params['payment']['cc_cid'],
                'billingInfo' => array(
                        'firstname' => $params['firstname'],
                        'lastname' => $params['lastname'],
                        'street' => $params['street'][0],
                        'city' => $params['city'],
                        'region' => $params['region_id'],
                        'postcode' => $params['postcode'],
                        'telephone' => $params['telephone']                    
                    ),
                'shippingInfo' => array(
                        'firstname' => $params['firstname'],
                        'lastname' => $params['lastname'],
                        'street' => $params['street'][0],
                        'city' => $params['city'],
                        'region' => $params['region_id'],
                        'postcode' => $params['postcode'],
                        'telephone' => $params['telephone']  
                    )
                );                       
            return $customerData;
        }
        
        public function editProfile($params)
        {                        
            $customerData = $this->getCustomerData($params);
            $profileData = $this->getProfileData($customerData['ccId']);
            /**
             * verifies that it exists in Authorize.net
             */
            $this->getCustomerProfileRequest($profileData['token_profile_id']);
            
            if(!empty($profileData)){
                    $tokenProfileId = $profileData['token_profile_id'];
                    $tokenPaymentProfileId = $profileData['token_payment_profile_id'];

                    $xml = $this->getAuthModel();
                    $this->updateCustomerPaymentProfileRequest($xml, $tokenProfileId, $tokenPaymentProfileId, $customerData);
            }

            $this->updateCardDatabase($customerData['customerId'],$customerData['ccId'], $customerData['ccType'], $customerData['ccNumber'], $customerData['ccExp'], $customerData['ccVer']);			
        }
        
        public function newProfile($params)
        {
            $customerData = $this->getCustomerData($params);
            Mage::log($customerData, null, 'customerData.log');
            $profile = Mage::getModel('authorizenetcim/authorizenetcim');
            $profile->load($customerData['customerId'],'customer_id');
            $tokenProfileId = $profile->getTokenProfileId();

            if($tokenProfileId == null){
                $this->createCustomerProfileRequest($customerData['customerId'], $customerData['customerEmail'],$customerData['billingInfo'], $customerData['shippingInfo'],$customerData['ccNumber'], $customerData['ccExp'], $customerData['ccVer'], $customerData['ccType']);
            }else{						
                $this->createCustomerPaymentProfileRequest($customerData['customerId'], $tokenProfileId, $customerData['billingInfo'], $customerData['ccNumber'], $customerData['ccExp'], $customerData['ccVer'], $customerData['ccType']);
            }

            $this->updateCardDatabase($customerData['customerId'],$customerData['ccId'], $customerData['ccType'], $customerData['ccNumber'], $customerData['ccExp'], $customerData['ccVer']);
            
        }
        
        public function updateCardDatabase($customerId,$ccId, $ccType, $ccNumber, $ccExp, $ccVer)
        {
            $profileUpload = Mage::getModel('authorizenetcim/authorizenetcim')->load($ccId);
            $profileUpload
                    ->setCustomerId($customerId)
                    ->setCcType($ccType)
                    ->setCcLast4(substr($ccNumber, -4, 4))
                    ->setCcExpMonth(substr($ccExp, -2))
                    ->setCcExpYear(substr($ccExp, 0, -3))
                    ->setStoreId(Mage::app()->getStore()->getStoreId())
                    ->setVerification($ccVer)
                    ->save(); 
        }
        
        public function updateCustomerPaymentProfileRequest($xml, $tokenProfileId, $tokenPaymentProfileId, $customerData)
        {
            $xml->updateCustomerPaymentProfileRequest(array(
                'customerProfileId' => $tokenProfileId,
                'paymentProfile' => array(
                    'billTo' => array(
                                    'firstName' => $customerData['billinginfo']['firstName'],
                                    'lastName' => $customerData['billinginfo']['lastName'],
                                    'address' => $customerData['billinginfo']['street'],
                                    'city' => $customerData['billinginfo']['city'],
                                    'state' => $customerData['billinginfo']['stateCode'],
                                    'zip' => $customerData['billinginfo']['zipCode'],
                                    'phoneNumber' => $customerData['billinginfo']['phoneNumber'],
                    ),
                    'payment' => array(
                                    'creditCard' => array(
                                                    'cardNumber' => $customerData['ccNumber'],
                                                    'expirationDate' => $customerData['ccExp'],
                                                    'cardCode' => $customerData['ccVer']
                                    )
                    ),
                    'customerPaymentProfileId' => $tokenPaymentProfileId
                )
            ));
            
            Mage::helper('authorizenetcim')->response($xml);
        }
}
