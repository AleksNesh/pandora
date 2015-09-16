<?php

/**
 * Extend/Override TinyBrick_Authorizenetcim module
 *
 * Adds ability to partially authorize/capture payments for invoices
 * using the CIM API (and some AIM API arguments)
 *
 * @see http://support.authorize.net/authkb/index?page=content&id=A510&pmv=print&impressions=false
 *
 * @category    Pan
 * @package     Pan_Authorizenetcim
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_Authorizenetcim_Model_Authorizenetcimsoap extends TinyBrick_Authorizenetcim_Model_Authorizenetcimsoap
{

    const ALLOW_PARTIAL_AUTH_CAPTURE_XML_PATH = 'payment/authorizenetcim/allow_partial_auth_capture';

    /**
     * @var boolean $_canCapturePartial
     *
     * Set $_canCapturePartial to true so methods that check if this
     * payment method can handle partial payments or not will allow
     * additional functionality/data to be used or displayed.
     *
     * This is the magic setting that allows changing of the quantity of items invoiced
     *
     * @see Mage_Payment_Model_Method_Cc
     * @see Mage_Payment_Model_Method_Abstract
     * @see Mage_Payment_Model_Method_Abstract::canCapturePartial()
     */
    protected $_canCapturePartial = true;

    /**
     * This overwrites the authorize function and calls the callApi function
     * From here, it contacts authorize.net
     * Mage::helper('authorizenetcim')->response($response) - checks the response to make sure it is valid
     *
     * @see Mage_Payment_Model_Method_Abstract::authorize()
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $customer           = $payment->getOrder()->getCustomerId();//->getCustomer()->getData();
        $this->_storeId     = $payment->getOrder()->getStoreId();

        /**
         * Switches method to be used to create authorization based on
         * if the customer is registered/logged in or is a guest.
         *
         * This $type variable is passed into parent::callApi() and a
         * switch statement is used to determine which method should be
         * used to generate the XML for the request.
         *
         * Registered Customers will use the self::createAuthorize() method
         * Guests will have to use the self::createAuthorizeAim() method
         */
        $type = ($customer) ? 'authorize' : 'createauthorizeaim';

        // Mage::log('From '. __CLASS__ .'::'. __FUNCTION__ .'() AT LINE '. __LINE__);
        // Mage::log('$type: ' . print_r($type, true));


        // Call the Authorize.Net API
        $response = $this->callApi($payment, $amount, $type);

        // Checks to see if we can connect to Authorize.net
        Mage::helper('authorizenetcim')->response($response);

        if ($type === 'createauthorizeaim') {
            // Mage::log('From '. __CLASS__ .'::'. __FUNCTION__ .'() AT LINE '. __LINE__);
            // Mage::log("Response:\n" . print_r($response, true));
            // Mage::log("Transaction Response:\n" . print_r($response->transactionResponse, true));

            $invoiceNumber  = $response->invoiceNumber;
            $transactionId  = (string)$response->transactionResponse->transId;
            $authCode       = (string)$response->transactionResponse->authCode;
            $splitTenderId  = '';

        } else {
            $directResponseFields = $this->getMappedDirectResponse($response->directResponse);

            // Mage::log('From ' . __CLASS__ . '::' . __FUNCTION__ . '() AT LINE ' . __LINE__);
            // Mage::log("Direct Response Fields:\n" . print_r($directResponseFields, true));

            $transactionId  = $directResponseFields['TransactionID'];
            $authCode       = $directResponseFields['AuthorizationCode'];
            $splitTenderId  = $directResponseFields['SplitTenderID'];
        }




        $payment->setTransactionId($transactionId);
        $payment->setIsTransactionClosed(0);
        $payment->setCcTransId($transactionId);

        // keep track of AuthorizationCode and SplitTenderID values from the direct response
        $payment->setData('cc_authorization_code', $authCode);
        $payment->setData('cc_split_tender_id', $splitTenderId);

        $orderId = $payment->getOrder()->getIncrementId();

        $teoAuths = Mage::getModel('authorizenetcim/teoauths');
        $teoAuths->setOrderId($orderId);
        $teoAuths->setAuthorizationNumber($transactionId);
        $teoAuths->setType('authorize');
        $teoAuths->setAuthorizationAmount($amount);

        // keep track of AuthorizationCode and SplitTenderID values from the direct response
        $teoAuths->setData('cc_authorization_code', $authCode);
        $teoAuths->setData('cc_split_tender_id', $splitTenderId);

        $teoAuths->save();

        return $this;
    }

    /**
     * capture
     *
     * Overwrites the capture function and calls the callApi function
     * Contacts authorize.net
     * Mage::helper('authorizenetcim')->response($response) - checks the response to make sure it is valid
     *
     * @see Mage_Payment_Model_Method_Abstract::capture()
     *
     * @param Varien_Object   $payment     # Payment object
     * @param integer         $amount      # Amount to capture
     * @param string          $type        # This is either 'useCIM' or 'useAIM'
     */
    public function capture(Varien_Object $payment, $amount, $type = NULL)
    {
        $order          = $payment->getOrder();
        $orderId        = $order->getIncrementId();
        $this->_storeId = $order->getStoreId();
        $paymentAction  = $this->getPaymentAction();

        // Check if guest checkout (Guests use AIM methods; registered customers use CIM methods)
        $type = (!$order->getCustomerId()) ? 'useAIM' : 'useCIM';

        switch ($paymentAction) {
            // authorize only
            case 'authorize':
                if($type === 'useAIM') {
                    // $apiMethod = 'captureAIM';
                    $apiMethod = 'captureWithAmountAIM';
                } else {
                    $allowPartial   = Mage::getStoreConfigFlag(self::ALLOW_PARTIAL_AUTH_CAPTURE_XML_PATH);
                    $apiMethod      = ($allowPartial) ? 'partialcapture' : 'capture';
                }

                break;
            // authorize and capture
            default:
                $apiMethod = ($type === 'useAIM') ? 'authorizeandcaptureAIM' : 'authorizeandcapture';
                break;
        }

        // Call the Authorize.Net API
        $response = $this->callApi($payment, $amount, $apiMethod);

        // Checks to see if we can connect to Authorize.net
        Mage::helper('authorizenetcim')->response($response);

        // keep track of Transaction ID and other important values from the response
        if ($type === 'useCIM') {
            $directResponseFields = $this->getMappedDirectResponse($response->directResponse);

            // Mage::log('From '. __CLASS__ .'::'. __FUNCTION__ .'() IN FILE '. __FILE__ .' AT LINE '. __LINE__);
            // Mage::log("Direct Response Fields:\n" . print_r($directResponseFields, true));

            $transactionId  = $directResponseFields['TransactionID'];
            $authCode       = $directResponseFields['AuthorizationCode'];
            $splitTenderId  = $directResponseFields['SplitTenderID'];
        } else {
            $transactionId  = $response->transactionResponse->transId;
            // @TODO: test as Guest and find XML nodes that correspond to AuthorizationCode and SplitTenderId
            $authCode       = '';
            $splitTenderId  = '';
        }

        // keep track of AuthorizationCode and SplitTenderID values from the response
        $payment->setData('cc_authorization_code', $authCode);
        $payment->setData('cc_split_tender_id', $splitTenderId);

        $payment->setCcTransId($transactionId);  // probably shouldn't set. (this is their comment. I don't know why or why not)
        $payment->setTransactionId($transactionId);

        // update the TEO Authorization amount_paid value
        $teoAuth = $this->updateTeoAuthorizationAmountPaid($orderId, $amount);

        return $this;
    }

    /**
     * callApi is the major piece in the puzzle
     *
     * prepares information and call specific xml api
     *
     * @param object $payment Payment Object
     * @param int $amount Amount to charge
     * @param string $type either CIM or AIM
     * @param int $ccSaveId Used to determine whether or not a profile exists for the customer
     * @param int $tokenProfileId Checks if the payment profile already exists, if not, creates it
     */
    public function callApi(Varien_Object $payment, $amount, $type)
    {
        /**
         * =====================================================================
         * BEGIN AAI HACK
         *
         * Cleaned up so it would be a little easier to understand
         * =====================================================================
         */

        $order      = $payment->getOrder();
        $orderId    = $order->getIncrementId();
        $postData   = Mage::app()->getRequest()->getPost('payment', array());
        $ccSaveId   = (array_key_exists('ccsave_id', $postData)) ? $postData['ccsave_id'] : null;

        if($type != 'authorizeandcaptureAIM'){
            $customerID = $order->getCustomerId();

            // for Guests, set the customerId to the Order's increment_id
            if(!$customerID) {
                $customerID = $orderId;
            }

            // order values
            $customerEmail  = $order->getCustomerEmail();
            $billingInfo    = $order->getBillingAddress();
            $shippingInfo   = $order->getShippingAddress();

            // payment values
            $ccType         = $payment->getCcType();
            $ccNumber       = $payment->getCcNumber();
            $ccExpDate      = $payment->getCcExpYear() .'-'. str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT);
            $ccCCV          = $payment->getCcCid();

            // CIM token values
            $tokenProfileId         = $payment->getTokenProfileId();
            $tokenPaymentProfileId  = $payment->getTokenPaymentProfileId();

            if( ($tokenProfileId == 0 && $tokenPaymentProfileId == 0)
                && (in_array($type, array('authorize', 'capture', 'authorizeandcapture'))) ) {

                if(!is_null($ccSaveId)){
                    $profile                = $this->getAuthnetcimCardProfileById($ccSaveId);
                    $profileData            = $profile->getData();
                    $tokenProfileId         = $profileData['token_profile_id'];
                    $tokenPaymentProfileId  = $profileData['token_payment_profile_id'];
                } else {
                    $profileCollection = $this->getAuthnetcimCardProfilesByCustomerId($customerID);

                    if (count($profileCollection) === 0) {
                        // Create new customer profile
                        $responseXML = $this->createCustomerProfileRequest($customerID, $customerEmail, $billingInfo, $shippingInfo, $ccNumber, $ccExpDate, $ccCCV, $ccType);

                        $tokenProfileId         = $responseXML->customerProfileId;
                        $tokenPaymentProfileId  = $responseXML->customerPaymentProfileIdList->numericString;
                    } else {
                        $tokenProfileId         = $profileCollection->getFirstItem()->getTokenProfileId();
                        $tokenPaymentProfileId  = null;

                        $ccLast4 = substr($ccNumber, -4, 4);
                        foreach ($profileCollection as $profile) {
                            if ($profile->getData("cc_last4") == $ccLast4) {
                                $tokenPaymentProfileId = $profile->getData("token_payment_profile_id");
                            }
                        }

                        if (is_null($tokenPaymentProfileId)) {
                            $tokenPaymentProfileId = $this->createCustomerPaymentProfileRequest($customerID, $tokenProfileId, $billingInfo, $ccNumber, $ccExpDate, $ccCCV, $ccType);
                        }
                    }
                }
            }
        }

        /**
         * =====================================================================
         * END AAI HACK
         *
         * Cleaned up so it would be a little easier to understand
         * =====================================================================
         */

        // call xml creation functions
        switch($type) {
            case 'authorize':
                $payment->setTokenProfileId($tokenProfileId);
                $payment->setTokenPaymentProfileId($tokenPaymentProfileId);

                $response = $this->createAuthorize($amount, $tokenProfileId, $tokenPaymentProfileId, $orderId, $ccCCV);
                break;
            case 'capture':
                $teoAuths = Mage::getModel('authorizenetcim/teoauths');
                $authsCollection = $teoAuths->getCollection()->addFieldToFilter('order_id', $orderId);

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
                                $response = $this->createCapture($amountLeftOnAuth, $tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId);

                                $teoAuths->setAmountPaid($amountLeftOnAuth);
                                $teoAuths->save();

                                $amountLeftToCapture = $amountLeftToCapture - $amountLeftOnAuth;
                            }
                            else {
                                $response = $this->createCapture($amountLeftToCapture, $tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId);

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
                    $response = $this->createCapture($amount, $tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId);
                }
                break;

            /**
             * =================================================================
             * BEGIN AAI HACK
             *
             * ADD SPECIFIC METHOD FOR PARTIAL AUTH/CAPTURE(S)
             * =================================================================
             */
            case 'partialcapture':
                $teoAuth        = $this->getTeoAuthorizationByOrderId($orderId);
                $transId        = $teoAuth->getData('authorization_number');

                // Generate the XML for the API request and make a call to the API for a response
                $response = $this->createPartialCapture($amount, $tokenProfileId, $tokenPaymentProfileId, $transId, $order);
                break;
            /**
             * =================================================================
             * END AAI HACK
             *
             * ADD SPECIFIC METHOD FOR PARTIAL AUTH/CAPTURE(S)
             * =================================================================
             */

            case 'authorizeandcapture':
                $payment->setTokenProfileId($tokenProfileId);
                $payment->setTokenPaymentProfileId($tokenPaymentProfileId);

                $response = $this->createAuthorizeCapture($amount, $tokenProfileId, $tokenPaymentProfileId, $orderId, $ccCCV);
                break;
            case 'void':
                $refundTransactionId = $payment->getRefundTransactionId();
                $response = $this->createVoid($tokenProfileId, $tokenPaymentProfileId, $refundTransactionId);
                break;
            case 'refund':
                $teoAuths = Mage::getModel('authorizenetcim/teoauths');
                $authsCollection = $teoAuths->getCollection()->addFieldToFilter('order_id', $orderId);

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
                                $response = $this->createRefund($amountLeftOnAuth, $tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId);

                                $teoAuths->setAmountRefunded($amountLeftOnAuth);
                                $teoAuths->save();

                                $amountLeftToRefund = $amountLeftToRefund - $amountLeftOnAuth;
                            }
                            else {
                                $response = $this->createRefund($amountLeftToRefund, $tokenProfileId, $tokenPaymentProfileId, $authorizeTransactionId);

                                $teoAuths->setAmountRefunded($amountLeftToRefund);
                                $teoAuths->save();

                                $amountLeftToRefund = 0;
                            }
                        }
                    }
                }
                else{
                    $refundTransactionId = $payment->getRefundTransactionId();
                    $response = $this->createRefund($amount, $tokenProfileId, $tokenPaymentProfileId, $refundTransactionId);
                }
                break;
            case 'authorizeandcaptureAIM':
                $response = $this->createAuthorizeCaptureAIM($amount, $payment, $order);
                break;
            case 'captureAIM':
                $response   = $this->captureAIM($tokenProfileId);
                break;
            /**
             * AAI HACK
             *
             * clone of 'captureAIM' but adds an amount to be captured
             * instead of assuming that the full amount will be captured
             */
            case 'captureWithAmountAIM':
                $teoAuth    = $this->getTeoAuthorizationByOrderId($orderId);
                $transId    = $teoAuth->getData('authorization_number');

                $response   = $this->captureWithAmountAIM($transId, $amount);
                break;
            /**
             * END AAI HACK
             */
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
     * captureWithAmountAIM
     *
     * clone of the captureAIM method but adds an amount to be captured
     * instead of assuming that the full amount should be captured
     *
     * @param  string   $transactionId
     * @param  float    $amount
     * @return SimpleXMLElement
     */
    public function captureWithAmountAIM($transactionId, $amount)
    {
        // Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
        // Mage::log('$transactionId: ' . print_r($transactionId, true));
        // Mage::log('$amount: ' . print_r($amount, true));

        $xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml', array('store' => $this->_storeId));
        $xml->createTransactionRequest(array(
            'transactionRequest' => array(
                'transactionType'   => 'priorAuthCaptureTransaction',
                'amount'            => $amount,
                'refTransId'        => $transactionId,
            ),
        ));

        Mage::helper('authorizenetcim')->result($xml);

        return $xml;
    }

    /**
     * Utilize the captureOnly method for partial authorizations.
     *
     * The payment method needs to be 'authorization' (auth_only)
     * and the original authorization response should return values
     * for 'SplitTenderID' and 'AuthorizationCode', both of which are
     * REQUIRED to capture chunks of previously authorized transaction
     *
     * @see http://support.authorize.net/authkb/index?page=content&id=A510&pmv=print&impressions=false
     *
     * @param   int             $amount                 # Amount (full or partial amount of original authorized amount)
     * @param   int             $tokenProfileId         # Customer profile Id
     * @param   int             $tokenPaymentProfileId  # Customer Payment Profile ID to use
     * @param   int             $transationId           # Authorize.Net transaction ID
     * @param   Varien_Object   $order                  # Mage_Sales_Model_Order object
     * @return  Pan_Authorizenetcim_Model_Authorizenetcim_Authnetxml
     */
    public function createPartialCapture($amount, $tokenProfileId, $tokenPaymentProfileId, $transactionId, Varien_Object $order)
    {
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection();
        $collection->addFieldToFilter('order_id', $order->getId())
            ->addFieldToFilter('txn_type', 'authorization')
            ->addFieldToFilter('is_closed', 0);

        if ($collection->count() > 0) {
            $authTxn    = $collection->getFirstItem();
            $trxnId     = $authTxn->getData('txn_id');
        }

        // just make sure we are using one of the transaction ids
        $trxnId = (isset($trxnId)) ? $trxnId : $transactionId;

        $grandTotal             = $order->getData('grand_total');
        $amountPaid             = $order->getData('total_paid');
        $remainingAmount        = $grandTotal - ($amountPaid + $amount);
        $needsReauthorization   = ($remainingAmount > 0) ? true : false;

        // Mage::log('$amount to be captured ' . print_r($amount, true));
        // Mage::log('$grandTotal for order ' . print_r($grandTotal, true));
        // Mage::log('$amountPaid already captured ' . print_r($amountPaid, true));
        // Mage::log('$remainingAmount to be captured ' . print_r($remainingAmount, true));

        try {
            if ($needsReauthorization) {
                $reauthorizedForRemaining = $this->_reauthorizeRemainingAmount($order, $remainingAmount);

                // force error (for testing)
                // $reauthorizedForRemaining = false;

                if(!$reauthorizedForRemaining) {
                    $message = 'Unable to save invoice/capture payment because the remaining funds could not be authorized! Skipping capturing of payment!';

                    Mage::getSingleton('admin/session')->addError($message);
                    throw new Pan_Authorizenetcim_Model_Authorizenetcim_Transaction_Exception($message);
                }
            }
        } catch (Pan_Authorizenetcim_Model_Authorizenetcim_Transaction_Exception $e) {
            // rethrow exepction
            throw new Pan_Authorizenetcim_Model_Authorizenetcim_Transaction_Exception($e->getMessage());
        } catch (Exception $e) {
            Mage::log('From ' . __CLASS__ . '::' . __FUNCTION__ . '() AT LINE ' . __LINE__);
            Mage::log($e->getMessage());
        }

        $xml = Mage::getModel('authorizenetcim/authorizenetcim_authnetxml', array('store' => $this->_storeId));
        $xml->createCustomerProfileTransactionRequest(
            array(
                'transaction' => array(
                    'profileTransPriorAuthCapture' => array(
                        'amount'                    => $amount,
                        'customerProfileId'         => $tokenProfileId,
                        'customerPaymentProfileId'  => $tokenPaymentProfileId,
                        'transId'                   => $trxnId,
                    )
                ),
            )
        );

        // Mage::log('From ' . __CLASS__ . '::' . __FUNCTION__ . '() IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
        // Mage::log('createPartialCapture XML (' . $captureMethod . ') '  . print_r($xml, true));

        return $xml;
    }


    /**
     * _reauthorizeRemainingAmount
     *
     * check to see if the card can be authorized for the remaining amount
     * if not, raise an exception
     *
     * @param  Mage_Sales_Model_Order   $order
     * @param  string|float             $amount
     * @param  string|integer           $profId
     * @param  string|integer           $payProfId
     * @param  string|integer           $trxId
     * @return boolean
     */
    protected function _reauthorizeRemainingAmount(Mage_Sales_Model_Order $order, $amount)
    {
        // default return value
        $reauthSuccessful = false;

        $ccCCV          = $order->getPayment()->getCcCid();
        $orderId        = $order->getIncrementId();
        $origPayment    = $order->getPayment();

        try {
            // Mage::log($origPayment->debug());

            $payment = Mage::getModel('sales/order_payment');
            $payment->setOrder($order);
            $payment->setData('method', $origPayment->getData('method'));
            $payment->setData('cc_exp_month', $origPayment->getData('cc_exp_month'));
            $payment->setData('cc_exp_year', $origPayment->getData('cc_exp_year'));
            $payment->setData('cc_last4', $origPayment->getData('cc_last4'));
            $payment->setData('cc_type', $origPayment->getData('cc_type'));
            $payment->setData('token_profile_id', $origPayment->getData('token_profile_id'));
            $payment->setData('token_payment_profile_id', $origPayment->getData('token_payment_profile_id'));

            // create a new authorization payment
            $payment->authorize(true, $amount);

            $payment->save();

            if ($payment->getData('transaction_id')) {
                // Mage::log('From ' . __CLASS__ . '::' . __FUNCTION__ . '() AT LINE ' . __LINE__);
                // Mage::log('Reauthorization for ' . $amount . ' was successful!');

                // If we have a transactional id, then it was successful
                $reauthSuccessful = true;
            }
        } catch (Exception $e) {
            Mage::log('From ' . __CLASS__ . '::' . __FUNCTION__ . '() AT LINE ' . __LINE__);
            Mage::log($e->getMessage());

            $reauthSuccessful = false;
        }

        return $reauthSuccessful;
    }


    /**
     * update the TEO Authorization amount_paid value
     */
    public function updateTeoAuthorizationAmountPaid($orderId, $amount, $mathOperation = 'add')
    {
        // load the TEO Authorization from the Order ID
        $teoAuth        = $this->getTeoAuthorizationByOrderId($orderId);
        $currentAmtPaid = $teoAuth->getData('amount_paid');

        switch(true) {
            case (in_array(strtolower($mathOperation), array('minus', 'subtract', '-', 'refund'))):
                $updatedAmtPaid = $currentAmtPaid - $amount;
                break;
            case (in_array(strtolower($mathOperation), array('add', 'addition', 'plus', '+', 'capture'))):
                // allow fall through to default method
            default:
                $updatedAmtPaid = $currentAmtPaid + $amount;
                break;
        }

        $teoAuth->setData('amount_paid', $updatedAmtPaid);
        $teoAuth->save();

        return $teoAuth;
    }


    /**
     * fetch a single tinybrick_authorizenetcim_ccsave record from the database
     * by the record's primary key value
     *
     * @param  integer  $id     # primary key value
     * @return TinyBrick_Authorizenetcim_Model_Authorizenetcim
     */
    public function getAuthnetcimCardProfileById($id)
    {
        return Mage::getModel('authorizenetcim/authorizenetcim')->load($id);
    }

    /**
     * fetch a single tinybrick_authorizenetcim_ccsave record from the database
     * by the record's customer_id value
     *
     * @param  integer  $customerId
     * @return TinyBrick_Authorizenetcim_Model_Authorizenetcim
     */
    public function getAuthnetcimCardProfilesByCustomerId($customerId)
    {
        $collection = $this->_getAuthorizenetcimCcsaveCollection();
        // filter the collection by the customer_id
        $cardProfiles = $collection->addFieldToFilter('customer_id', $customerId);

        return $cardProfiles;
    }

    /**
     * fetch a single TEO Authorization from the database
     * by the record's order_id value
     *
     * @param  integer  $orderId     # Magento Order's increment_id
     * @return TinyBrick_Authorizenetcim_Model_Teoauths
     */
    public function getTeoAuthorizationByOrderId($orderId)
    {
        // get records from the `oc_teo_authorizations` table
        $collection = $this->_getTeoAuthorizationCollection();
        $teoAuth = $collection->addFieldToFilter('order_id', $orderId)->getFirstItem();

        return $teoAuth;
    }

    protected function _getTeoAuthorizationCollection()
    {
        return Mage::getModel('authorizenetcim/teoauths')->getCollection();
    }

    protected function _getAuthorizenetcimCcsaveCollection()
    {
        return Mage::getModel('authorizenetcim/authorizenetcim')->getCollection();
    }


    /**
     * getMappedDirectResponse
     *
     * wrapper method to _getMappedDirectResponse() that will create
     * a mapped array of direct response fields with their keys
     *
     * @var     string     $directResponseString
     * @return  array
     */
    public function getMappedDirectResponse($directResponseString)
    {
        return $this->_getMappedDirectResponse($directResponseString);
    }

    /**
     * _getMappedDirectResponse
     *
     * creates a mapped array of direct response fields with their keys
     *
     * @var     string     $directResponseString
     * @return  array
     */
    protected function _getMappedDirectResponse($directResponseString)
    {
        // create an array from a comma-separated string of values
        $directResponseFields   = explode(",", $directResponseString);
        // fetch the mapped array of known direct response fields
        $directResponseKeys     = Mage::helper('pan_authorizenetcim')->getDirectResponseArrayKeys();

        /**
         * map the response keys to appropriate fields and take into
         * consideration that 40-49 are merchant defined fields so they
         * probably won't have a key to be used
         */
        $mappedFields = array();
        foreach($directResponseFields as $k => $val) {
            $newKey = (array_key_exists($k, $directResponseKeys)) ? $directResponseKeys[$k] : $k;
            $mappedFields[$newKey] = $val;
        }

        return $mappedFields;
    }
}
