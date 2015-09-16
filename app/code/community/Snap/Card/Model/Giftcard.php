<?php
/**
 * Giftcard model
 *
 * @category    Snap
 * @package     Snap_Card
 * @author      alex
 */

class Snap_Card_Model_Giftcard extends Mage_Core_Model_Abstract
{
    /**
     * Status code for activated card
     */
    const STATUS_ACTIVATED =  'Activated';

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'snap_card';

    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'snap_card';


    /**
     * Current operation amount
     *
     * @var null|float
     */
    protected $_amount = null;
    
    /**
     * New card amount.
     * @var type 
     */
    protected $_newAmount = null;

    /**
     * Card code that was requested for load
     *
     * @var bool|string
     */
    protected $_requestedCode = false;
    
    /**
     * Card pin that was requested for load
     * @var type 
     */
    protected $_requestedPin = false;
    
    /**
     * Encrypted version of card pin.
     * @var type 
     */
    protected $_encryptedPin = false;

    /**
     * Constructor. Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('snap_card/giftcard');
    }

    /**
     * Load snap gift card model using specified code
     *
     * @param string $code
     * @return $this
     */
    public function loadByCode($code, $pin = null)
    {
        $this->_requestedCode = $code;
        $this->_requestedPin = $pin;
        $this->_encryptedPin = Mage::helper('snap_card')->encryptPin("" . $pin);
        
        //return $this->load($code, 'code');
        return $this;
    }

    /**
     * Add gift card to quote gift card storage
     *
     * @param bool $saveQuote
     * @param null $quote
     * @return $this
     */
    public function addToCart($saveQuote = true, $quote = null)
    {
        if (!$quote) {
            $quote = $this->_getCheckoutSession()->getQuote();
        }
        if ($this->isValid(true, true, false)) {
            $cards = Mage::helper('snap_card')->getCards($quote);
            if (!$cards) {
                $cards = array();
            } else {
                foreach ($cards as $card) {
                    if ($card['c'] == $this->_requestedCode) {
                        Mage::throwException(
                            Mage::helper('snap_card')->__('This gift card is already in the quote.')
                        );
                    }
                }
            }
            
            
            $cards[] = array(
                // id
                //'i' => $this->getId(),
                // code
                'c' => $this->_requestedCode,
                // amount
                'a' => $this->_amount,
                // base amount
                'ba' => $this->_amount,
                // Pin
                'pin' => $this->_encryptedPin,
                // API amount
                'api_amount' => $this->_amount
            );
            Mage::log("Got new cards - " . sizeof($cards));
            Mage::helper('snap_card')->setCards($quote, $cards);

            if ($saveQuote) {
                $quote->collectTotals()->save();
            }
        }

        return $this;
    }

    /**
     * Update gift card amount applied to current quote
     *
     * @param bool $saveQuote
     * @param null $quote
     * @return $this
     */
    public function updateQuote($saveQuote = true, $quote = null)
    {
        if (!$quote) {
            $quote = $this->_getCheckoutSession()->getQuote();
        }

        if ($this->isValid(true, true, false)) {
            $cards = Mage::helper('snap_card')->getCards($quote);
            if (!$cards) {
                $cards = array();
            } else {
                foreach ($cards as $index => $card) {
                    if ($card['c'] == $this->_requestedCode) {
                        $cards[$index]["a"] = $this->_newAmount;
                        $cards[$index]["ba"] = $this->_newAmount;
                    }
                }
            }
            /*
            $amount = $this->_amount;
            $cards[] = array(
                // id
                //'i' => $this->getId(),
                // code
                'c' => $this->_requestedCode,
                // amount
                'a' => $this->_amount,
                // base amount
                'ba' => $this->_amount,
                // Pin
                'pin' => $this->_requestedPin,
                // API amount
                'api_amount' => $this->_amount
            );*/
            Mage::helper('snap_card')->setCards($quote, $cards);
            if ($saveQuote) {
                $quote->collectTotals()->save();
            }
        }

        return $this;
    }


    /**
     * Remove gift card from quote gift card storage
     *
     * @param bool $saveQuote
     * @param Mage_Sales_Model_Quote|null $quote
     * @return Enterprise_GiftCardAccount_Model_Giftcardaccount
     */
    public function removeFromCart($saveQuote = true, $quote = null)
    {
        if (!$this->_requestedCode) {
            $this->_throwException(
                Mage::helper('snap_card')->__('Wrong gift card code: "%s".', $this->_requestedCode)
            );
        }
        if (is_null($quote)) {
            $quote = $this->_getCheckoutSession()->getQuote();
        }

        $cards = Mage::helper('snap_card')->getCards($quote);
        if ($cards) {
            foreach ($cards as $k => $card) {
                if ($card['c'] == $this->_requestedCode) {
                    unset($cards[$k]);
                    Mage::helper('snap_card')->setCards($quote, $cards);

                    if ($saveQuote) {
                        $quote->collectTotals()->save();
                    }
                    return $this;
                }
            }
        }

        $this->_throwException(
            Mage::helper('snap_card')->__('This gift card account wasn\'t found in the quote.')
        );
    }

    /**
     * Check all the gift card validity attributes
     *
     * @param bool $expirationCheck
     * @param bool $statusCheck
     * @param mixed $websiteCheck
     * @param mixed $balanceCheck
     * @return bool
     */
    public function isValid($expirationCheck = true, $statusCheck = true, $websiteCheck = false, $balanceCheck = true)
    {
        $oldBalance = Mage::helper('snap_card')->getBalance($this->_requestedCode, $this->_requestedPin, "USD");
        if($oldBalance === false) {
            $this->_throwException(
                Mage::helper('snap_card')->__('This giftcard is not valid. Requested card number: "%s".', $this->_requestedCode)
            );
        } else {
            if($balanceCheck && $oldBalance * 1 <= 0) {
                $this->_throwException(
                    Mage::helper('snap_card')->__('The gift card %s does not have any funds.', $this->_requestedCode)
                );
            } else {
                $this->_amount = $oldBalance * 1;
            }
        }
        
        /*
        if (!$this->getId()) {
            $this->_throwException(
                Mage::helper('snap_card')->__('Wrong snap card ID. Requested code: "%s"', $this->_requestedCode)
            );
        }

        if ($statusCheck && ($this->getStatus() != self::STATUS_ACTIVATED)) {
            $this->_throwException(
                Mage::helper('snap_card')->__('Snap card %s is not enabled.', $this->getId())
            );
        }


        //@TODO Check if this validation needed
        /*if ($expirationCheck && $this->isExpired()) {
            $this->_throwException(
                Mage::helper('snap_card')->__('Snap card %s is expired.', $this->getId())
            );
        }
        * /

        if ($balanceCheck) {
            if ($this->getBalance() <= 0) {
                $this->_throwException(
                    Mage::helper('enterprise_giftcardaccount')->__('Snap card %s balance does not have funds.', $this->getId())
                );
            }
        }*/

        return true;
    }

    /**
     * Obscure real exception message to prevent brute force attacks
     *
     * @throws Mage_Core_Exception
     * @param string $realMessage
     * @param string $fakeMessage
     */
    protected function _throwException($realMessage, $fakeMessage = '')
    {
        $e = Mage::exception('Mage_Core', $realMessage);
        Mage::logException($e);
        if (!$fakeMessage) {
            $fakeMessage = Mage::helper('snap_card')->__('The gift card information you entered is not valid.');
        }
        $e->setMessage($fakeMessage);
        throw $e;
    }

    /**
     * Return checkout/session model singleton
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Set new card amount for current operation
     *
     * @param null|float $amount
     * @return $this
     */
    public function setNewAmount($amount = null)
    {
        $this->_newAmount = max(0, round($amount * 1, 2));
        return $this;
    }
    
    /**
     * Get the amount of this card.
     * @return type
     */
    public function getPropAmount() {
        return $this->_amount;
    }
    
    /**
     * Get the requested code.
     * @return type
     */
    public function getPropCode() {
        return $this->_requestedCode;
    }

    /**
     * Get card amount for current operation
     *
     * @return float
     */
    /*public function getAmount()
    {
        if ($this->_amount) {
            return $this->_amount;
        }
        return $this->getData('amount');
    }*/
}
