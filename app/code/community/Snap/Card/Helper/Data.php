<?php
/**
 * Helper model
 *
 * @category   Snap
 * @package    Snap_Card
 */
class Snap_Card_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check if giftcard module enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Check if customer has applied to quote giftcards
     *
     * @return bool
     */
    public function hasAppliedGiftCards()
    {
        return count($this->getAppliedGiftCards()) > 0;
    }

    /**
     * Get applied to quote giftcards
     *
     * @return array
     */
    public function getAppliedGiftCards()
    {
        return $this->getCards(Mage::getSingleton('checkout/session')->getQuote());
    }

    /**
     * Format price
     *
     * @param decimal $price
     * @return string
     */
    public function formatPrice($price)
    {
        return Mage::app()->getStore()->formatPrice($price, false);
    }

    /**
     * Unserialize and return snap gift card list from specified object
     *
     * @param Varien_Object $from
     * @return mixed
     */
    public function getCards(Varien_Object $from)
    {
        $value = $from->getSnapCards();
        if (!$value) {
            return array();
        }
        return unserialize($value);
    }
    
    /**
     * Get one card from the quote by its card number.
     * 
     * @param Varien_Object $from
     * @param type $card_number
     * @return type
     */
    public function getCard(Varien_Object $from, $card_number) {
        $result = false;
        
        $value = $from->getSnapCards();
        if (!$value) {
            return array();
        }
        $cards = unserialize($value);
        foreach($cards as $card) {
            if($card["c"] == $card_number) {
                $result = $card;
                break;
            }
        }
        
        return $result;
    }

    /**
     * Serialize and set snap gift card list to specified object
     *
     * @param Varien_Object $to
     * @param mixed $value
     */
    public function setCards(Varien_Object $to, $value)
    {
        $serializedValue = serialize($value);
        $to->setSnapCards($serializedValue);

    }
    
    /**
    * Internal method.
    * Place a SOAP request to the SNAP API.
    * @param type $method Soap method to invoke (transaction type). E.g. Inquiry
    * @param type $params Additional parameters to pass in. The standardHeader is always added automatically
    * @return type
    */
   private function getResponse($method, $params) {
       $isTest = Mage::getStoreConfig('Snap/settings/testmode_select') == 0;
       $wsdl = $isTest ? "https://api-test.profitpointinc.com:8417/v4/transaction?wsdl" : "https://api.profitpointinc.com:8417/v4/transaction?wsdl";
       $options = array(
           "login" => Mage::getStoreConfig('Snap/settings/integrationuser_input'),
           "password" => Mage::getStoreConfig('Snap/settings/integrationpass_input'),
           "features" => SOAP_SINGLE_ELEMENT_ARRAYS
       );
       $client = new SoapClient($wsdl, $options);

       $inquiry = new stdClass();
       $inquiry->requestId          = '1';
       $inquiry->systemId           = Mage::getStoreConfig('Snap/settings/system_input');
       $inquiry->clientId           = Mage::getStoreConfig('Snap/settings/client_input');
       $inquiry->locationId         = Mage::getStoreConfig('Snap/settings/location_input');
       $inquiry->terminalId         = Mage::getStoreConfig('Snap/settings/terminal_input');
       $inquiry->initiatorType      = 'E';
       $inquiry->initiatorId        = Mage::getStoreConfig('Snap/settings/initiator_input');
       $inquiry->initiatorPassword  = Mage::getStoreConfig('Snap/settings/initiatorpass_input');
       
       $requestParams = array(
           "standardHeader" => $inquiry
       );
       foreach($params as $key => $value) {
           $requestParams[$key] = $value;
       }

       $response = null;
       try {
           $response = $client->__soapCall($method, array($requestParams), $options);
       } catch (Exception $e) {
           //TODO: Add logging with appropriate logging framework
       }
       return $response;
   }

   /**
    * Internal method.
    * Helper method, build an account component.
    * @param type $cardNumber
    * @param type $pin
    * @return \stdClass
    */
   private function buildAccountComponent($cardNumber, $pin) {
       $account = new stdClass();
       $account->accountId = $cardNumber . "";
       $account->pin = $pin . "";
       $account->entryType = "K";
       return $account;
   }

   /**
    * Internal method.
    * Helper method, build an amount component.
    * @param type $amount
    * @param type $valueCode
    * @return \stdClass
    */
   private function buildAmountComponent($amount, $valueCode) {
       $amountComp = new stdClass();
       $amountComp->valueCode = $valueCode . "";
       $amountComp->enteredAmount = round($amount * 1, 2) . "";

       return $amountComp;
   }

   /**
    * Return the total balance of a giftcard in the specified currency (value code).
    * @param type $cardNumber Always required, the card number of the giftcard
    * @param type $pin Card pin, is required by some stores. Leave as blank string if not required
    * @param type $valueCode Usually currency code, e.g. USD
    * @return number|bool False if card was invalid, otherwise numerical balance
    */
   public function getBalance($cardNumber, $pin, $valueCode) {
       $account = $this->buildAccountComponent($cardNumber, $pin);

       $response = $this->getResponse("Inquiry", array(
           "account" => $account
       ));

       $total_balance = 0;
       if(property_exists($response, "errorMessage") || !property_exists($response, "standardHeader")) {
           $total_balance = false;
           //TODO: Add logging with appropriate logging framework
       } else {
           $balances = $response->balances->balance;
           foreach($balances as $balance) {
               if($balance->valueCode == $valueCode) {
                   $total_balance += $balance->amount;
               }
           }
       }

       return $total_balance;
   }

   /**
    * Hold a certain amount on a giftcard.
    * This will reserve a certain amount of a gift card balance and return a transaction id.
    * The transaction ID can later be used to actually charge the card or cancel the hold.
    * @param type $cardNumber Always required, the card number of the giftcard
    * @param type $pin Card pin, is required by some stores. Leave as blank string if not required
    * @param type $amount Numerical amount to hold, for 100 USD, enter 100
    * @param type $valueCode Usually currency code, e.g. USD
    * @return boolean|String False if something went wrong, otherwise the transaction ID
    */
   public function holdBalance($cardNumber, $pin, $amount, $valueCode) {
        $quoteId = Mage::getSingleton("checkout/session")->getQuoteId();
        $resource = Mage::getSingleton("core/resource");
        $write = $resource->getConnection("core_write");
        $tableName = $resource->getTableName("snap_card/charge");
        
        $query = "INSERT INTO `" . $tableName . "` (charge_id, card_code, card_pin, amount, value_code, quote_id, client_addr, created_at, last_modified_at) " .
                "VALUES(UUID(), :cardCode, :cardPin, :amount, :valueCode, :quoteId, :clientAddr, NOW(), NOW())";
        $binds = array(
            "cardCode" => $cardNumber,
            "cardPin" => Mage::helper('snap_card')->encryptPin($pin),
            "amount" => $amount,
            "valueCode" => $valueCode,
            "quoteId" => $quoteId,
            "clientAddr" => $_SERVER["REMOTE_ADDR"]
        );
        $write->query($query, $binds);
        
        $response = $this->getResponse("Hold", array(
            "account" => $this->buildAccountComponent($cardNumber, $pin),
            "amount" => $this->buildAmountComponent($amount, $valueCode)
        ));
 
        $transactionId = false;
        if(property_exists($response, "errorMessage") || !property_exists($response, "standardHeader")) {
            Mage::log("Could not hold SNAP giftcard balance.");
            $write->query("UPDATE `" . $tableName . "` SET `is_error` = 1, last_modified_at = NOW() WHERE card_code = :cardCode AND quote_id = :quoteId", array(
                "cardCode" => $cardNumber,
                "quoteId" => $quoteId
            ));
        } else {
            $transactionId = $response->identification->transactionId;
            $write->query("UPDATE `" . $tableName . "` SET `is_holding` = 1, hold_transaction_id = :holdTransactionId, last_modified_at = NOW() " .
                    "WHERE card_code = :cardCode AND quote_id = :quoteId", array(
                "holdTransactionId" => $transactionId,
                "cardCode" => $cardNumber,
                "quoteId" => $quoteId
            ));
        }
 
        return $transactionId;
    }

   /**
    * Perform a hold redemption. This is the transactio that basically charges the giftcard
    * for a certain amount based on a previous hold request.
    * To cancel a hold, set the amount to 0.
    * NOTE: You can redeem more than once for a single hold. If the giftcard balance allows it,
    * you could possibly also redeem more from a single hold than the amount for which the hold was
    * originally requested.
    * @param type $cardNumber Always required, the card number of the giftcard
    * @param type $pin Card pin, is required by some stores. Leave as blank string if not required
    * @param type $amount Numerical amount to redeem, for 100 USD, enter 100
    * @param type $valueCode Usually currency code, e.g. USD
    * @param type $hold_transaction_id Transaction ID of transaction that performed the hold
    * @return boolean Success flag. True means the redemption was successful
    */
   public function holdRedemption($cardNumber, $pin, $amount, $valueCode, $hold_transaction_id) {
       $quoteId = Mage::getSingleton("checkout/session")->getQuoteId();
       $resource = Mage::getSingleton("core/resource");
       $write = $resource->getConnection("core_write");
       $tableName = $resource->getTableName("snap_card/charge");
       
       $response = $this->getResponse("HoldRedemption", array( 
           "account" => $this->buildAccountComponent($cardNumber, $pin),
           "amount" => $this->buildAmountComponent($amount, $valueCode),
           "transactionId" => ($hold_transaction_id . ""),
           "leavingHeld" => "N"
       ));

       $success = false;
       if(property_exists($response, "errorMessage") || !property_exists($response, "standardHeader")) {
           Mage::log("Could not redeem SNAP giftcard balance for " . $amount . " " . $valueCode);
           $write->query("UPDATE `" . $tableName . "` SET `is_error` = 1, last_modified_at = NOW() WHERE card_code = :cardCode AND quote_id = :quoteId", array(
               "cardCode" => $cardNumber,
               "quoteId" => $quoteId
           ));
       } else {
           $success = true;
           $write->query("UPDATE `" . $tableName . "` SET `is_charged` = 1, last_modified_at = NOW() " .
                   "WHERE card_code = :cardCode AND quote_id = :quoteId", array(
               "cardCode" => $cardNumber,
               "quoteId" => $quoteId
           ));
       }

       return $success;
   }
   
   /**
    * Return merchandise and undo a giftcard charge with it.
    * @param type $cardNumber
    * @param type $pin
    * @param type $amount
    * @param type $valueCode
    * @return boolean
    */
   public function merchandiseReturn($cardNumber, $pin, $amount, $valueCode) {
       $response = $this->getResponse("MerchandiseReturn", array( 
           "account" => $this->buildAccountComponent($cardNumber, $pin),
           "amount" => $this->buildAmountComponent($amount, $valueCode),
           "activating" => "N"
       ));
       $success = false;
       if(property_exists($response, "errorMessage") || !property_exists($response, "standardHeader")) {
           Mage::log("Could not place MerchandiseReturn call for card: " . $cardNumber . "... " . json_encode($response));
       } else {
           $success = true;
       }
       
       return $success;
   }
   
   /**
    * Attach an order ID to all giftcards in the current quote.
    * @param type $orderIdAttach 
    */
   public function attachOrderIdToCards($orderId) {
       $quoteId = Mage::getSingleton("checkout/session")->getQuoteId();
       $resource = Mage::getSingleton("core/resource");
       $write = $resource->getConnection("core_write");
       $tableName = $resource->getTableName("snap_card/charge");
       $write->query("UPDATE `" . $tableName . "` SET `order_id` = :orderId, last_modified_at = NOW() WHERE quote_id = :quoteId", array(
           "orderId" => $orderId,
           "quoteId" => $quoteId
       ));
   }
   
    /**
     * Encrypt a pin number.
     * @param type $pin
     * @return type
     */
    public function encryptPin($pin) {
        $key = "SNAP4secure3010c8sa";
        $encryptedPin = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), ($pin . ""), MCRYPT_MODE_CBC, md5(md5($key))));
        //$decryptedPin = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encryptedPin), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
        return $encryptedPin;
    }
    
    /**
     * Decrypt a pin number.
     * @param type $encryptedPin
     * @return type
     */
    public function decryptPin($encryptedPin) {
        $key = "SNAP4secure3010c8sa";
        $decryptedPin = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encryptedPin), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
        return $decryptedPin;
    }
    
    /**
     * Get the current order object.
     * @return type
     */
    public function getMyOrder() {
        Mage::log("Checking current order.");
        $order = Mage::registry('current_order');
        return $order;
    }
    
    /**
     * Get a list of all used SNAP gift cards for the current order.
     */
    public function getOrderCards() {
        $order = Mage::registry('current_order');
        $order_cards = array();
        
        if($order && $order->getId()) {
            $orderId = $order->getId();
            $resource = Mage::getSingleton("core/resource");
            $readConnection = $resource->getConnection("core_read");
            $tableName = $resource->getTableName("snap_card/charge");
            
            $results = $readConnection->fetchAll("SELECT * FROM `" . $tableName . "` WHERE `order_id` = :orderId", array(
                "orderId" => $orderId
            ));
            $order_cards = $results;
        }
        
        return $order_cards;
    }
    
    /**
     * Undo a certain charge.
     * @param type $chargeId
     */
    public function chargeBack($chargeId) {
        $resource = Mage::getSingleton("core/resource");
        $readConnection = $resource->getConnection("core_read");
        $tableName = $resource->getTableName("snap_card/charge");
        $charges = $readConnection->fetchAll("SELECT * FROM `" . $tableName . "` WHERE charge_id = :chargeId", array(
            "chargeId" => $chargeId
        ));
        $charge = sizeof($charges) > 0 ? $charges[0] : false;
        $success = $charge && $this->undoChargeDirect($charge);
        
        return $success;
    }
    
    /**
     * Undo a charge object.
     * @param type $charge
     * @return type
     */
    private function undoChargeDirect($charge) {
        $chargeId = $charge["charge_id"];
        $resource = Mage::getSingleton("core/resource");
        $tableName = $resource->getTableName("snap_card/charge");
        $writeConnection = $resource->getConnection("core_write");
        
        Mage::log("Undoing charge: " . $chargeId);
        $success = false;
        $decrypted_pin = $this->decryptPin($charge["card_pin"]);
        
        if($charge && $charge["is_error"] == 0 && $charge["is_returned"] == 0) {
            if($charge && $charge["is_charged"] > 0) {
                Mage::log("Was charged, placing a return...");
                $success = $this->merchandiseReturn($charge["card_code"], $decrypted_pin, $charge["amount"], $charge["value_code"]);
                if($success) {
                    $writeConnection->query("UPDATE `" . $tableName . "` SET is_returned = 1, last_modified_at = NOW() WHERE charge_id = :chargeId", array(
                        "chargeId" => $chargeId
                    ));
                }
            } else if($charge["is_holding"] > 0) {
                Mage::log("Was not charged yet, undoing the hold...");

                $success = $this->holdRedemption($charge["card_code"], $decrypted_pin, 0, $charge["value_code"], $charge["hold_transaction_id"]);
                if($success) {
                    $writeConnection->query("UPDATE `" . $tableName . "` SET is_holding = 0, last_modified_at = NOW() WHERE charge_id = :chargeId", array(
                        "chargeId" => $chargeId
                    ));
                }
            }
        }
        
        return $success;
    }
    
    /**
     * Perform a full return of one order, with all charge IDs that apply.
     * @param type $orderId Order ID for which to return all charges.
     * @return type
     */
    public function fullReturn($orderId) {
        Mage::log("Performing a full SNAP return for order: " . $orderId);
        $success = false;
        
        $resource = Mage::getSingleton("core/resource");
        $readConnection = $resource->getConnection("core_read");
        $tableName = $resource->getTableName("snap_card/charge");
        $charges = $readConnection->fetchAll("SELECT * FROM `" . $tableName . "` WHERE order_id = :orderId", array(
            "orderId" => $orderId
        ));
        Mage::log("Charges found: " . sizeof($charges));
        foreach($charges as $charge) {
            if($charge && $charge["is_error"] == 0 && $charge["is_returned"] == 0) {
                $success = $this->undoChargeDirect($charge);
                if(!$success) {
                    break;
                }
            }
        }
        
        return $success;
    }

}
