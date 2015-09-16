<?php

/**
 * Extend/Override TinyBrick_Authorizenetcim module
 *
 * @category    Pan
 * @package     Pan_Authorizenetcim
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Pan_Authorizenetcim_Helper_Data extends TinyBrick_Authorizenetcim_Helper_Data
{
    const RESPONSE_REASON_CODE_TRXN_ALREADY_CAPTURED = '311';

    /**
     * getDirectResponseArrayKeys
     *
     * @return array
     */
    public function getDirectResponseArrayKeys()
    {
        $drModel = Mage::getModel('pan_authorizenetcim/authorizenetcim_transaction_directresponse');
        return $drModel->getDirectResponseKeys();
    }


    /**
     * Checks the response for errors. if found, throws an exception.
     * @param string $response
     */
    public function result($response)
    {
        Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
        Mage::log($response);

        $trxnResponseMessageCode = $response->transactionResponse->messages->message->code;
        // Mage::log('trxnResponseMessageCode: ' . print_r($trxnResponseMessageCode, true));

        if(!$response->isSuccessful()){
            $result = $response->messages->resultCode;
            $resultCode = $response->messages->message->code;
            $resultText = $response->messages->message->text;

            Mage::throwException('Result: '.$result.' Code: '.$resultCode.' Message: '.$resultText);
        } else if($response->messages->resultCode != 'Ok'){
            $errorCode = $response->transactionResponse->errors->error->errorCode;
            $errorText = $response->transactionResponse->errors->error->errorText;

            Mage::throwException('Error Code: '.$errorCode.' Error Text: '.$errorText);
        }
        /**
         * AAI HACK
         * throw exception if the transaction has already been captured
         */
        else if($trxnResponseMessageCode == self::RESPONSE_REASON_CODE_TRXN_ALREADY_CAPTURED) {
            $messageCode = $response->transactionResponse->messages->message->code;
            $messageText = $response->transactionResponse->messages->message->description;

            Mage::throwException('Error Code: ' . $messageCode.' Error Text: ' . $messageText);
        }
        /**
         * END AAI HACK
         */

        if (!empty($response->transactionResponse->errors->error->errorCode) && $response->transactionResponse->errors->error->errorCode > 1) {
            $errorCode = $response->transactionResponse->errors->error->errorCode;
            $errorText = $response->transactionResponse->errors->error->errorText;

            Mage::throwException('Error Code: '.$errorCode.' Error Text: '.$errorText);
        }
    }

    public function saveToDatabase($customerID, $ccType, $ccNumber, $ccExpDate, $customerProfileID, $customerPaymentProfileID, $customerShippingAddressID, $storeId)
    {
        if ($storeId == 0) {
            $storeId = 1;
        }

        if (is_null($customerShippingAddressID)) {
            $customerShippingAddressID = 0;
        }

        $profileUpload = Mage::getModel('authorizenetcim/authorizenetcim');
        $profileUpload->setCustomerID($customerID)
            ->setCcType($ccType)
            ->setCcLast4(substr($ccNumber, -4, 4))
            ->setCcExpMonth(substr($ccExpDate, -2))
            ->setCcExpYear(substr($ccExpDate, 0, -3))
            ->setTokenProfileId($customerProfileID)
            ->setTokenPaymentProfileId($customerPaymentProfileID)
            ->setTokenShippingAddressId($customerShippingAddressID)
            ->setStoreId($storeId);

        try {
            $resource = Mage::getModel('core/resource');
            $newConnection = $resource->getConnection('core_write');
            $newConnection->insert('tinybrick_authorizenetcim_ccsave', $profileUpload->getData());
        } catch(Exception $e) {
            Mage::throwException($e->getMessage());
        }
    }

}
