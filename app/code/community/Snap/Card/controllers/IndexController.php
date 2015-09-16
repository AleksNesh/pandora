<?php
/**
 * Giftcard controller
 *
 * @category   Snap
 * @package    Snap_Card
 * @author     alex
 */
class Snap_Card_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function updateGcAmountAction()
    {
        $result = array(
            'updated'=>false
        );
        $amount = $this->getRequest()->getParam('amount');
        if (substr($amount, 0, 1) == '$') {
            $amount = substr($amount, 1);
        }
        $amount = (int)$amount;
        $cardNumber = $this->getRequest()->getParam('id');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $card = Mage::helper('snap_card')->getCard($quote, $cardNumber);
        
        if (!$card || $card["api_amount"] < $amount){
            $result['msg'] = $this->__('Not enough funds on balance');
        } else if ($amount > 0 ) {
            $decryptedPin = Mage::helper('snap_card')->decryptPin("" . $card["pin"]);
            Mage::getModel('snap_card/giftcard')
                ->loadByCode($cardNumber, $decryptedPin)
                ->setNewAmount($amount)
                ->updateQuote();
            
            /*$cards = Mage::helper('snap_card')->getCards($quote);
            foreach ($cards as $index => $card) {
                if ($card['c'] == $cardNumber) {
                    unset($cards[$index]);
                }
            }
            $card["amount"] = $amount;
            $cards[] = $card;
            Mage::helper('snap_card')->setCards($quote, $cards);
            $quote->collectTotals()->save();
            //->updateQuote();*/
            
            
            $result['amount'] = Mage::helper('snap_card')->formatPrice($amount);
            $result['updated'] = true;

            $result['html'] = $this->getLayout()->createBlock('checkout/cart_totals')
                ->setTemplate('checkout/cart/totals.phtml')
                ->toHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function removeGcAction()
    {
        $code = $this->getRequest()->getParam('code');
        $result = array(
            'removed'=>false
        );

        try {
            Mage::getModel('snap_card/giftcard')
                ->loadByCode($code)
                ->removeFromCart();

            $result['removed'] = true;
            $result['msg'] = $this->__('Gift Card "%s" was removed.', Mage::helper('core')->escapeHtml($code));
        } catch (Exception $e) {
            $result['msg'] =    $e->getMessage();
        }
        if (!$this->getRequest()->isAjax()) {
            if ($result['removed']) {
                 Mage::getSingleton('checkout/session')->addSuccess($result['msg']);
            } else {
                Mage::getSingleton('checkout/session')->addError($result['msg']);
            }
            return $this->_redirect('checkout/cart');
        } else {
            $result['html'] = $this->getLayout()->createBlock('checkout/cart_totals')
                ->setTemplate('checkout/cart/totals.phtml')
                ->toHtml();
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }

    }

    /**
     * Add Snap Gift Card to current quote
     *
     */
    public function addAction()
    {
        $data = $this->getRequest()->getPost();
        $result = array(
            'added' => false
        );
        if (isset($data['snap_card'])) {
            $code = $data['snap_card'];
            $pin = $data["snap_card_pin"];
            try {
                $card = Mage::getModel('snap_card/giftcard')
                    ->loadByCode($code, $pin)
                    ->addToCart();
                
                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Gift Card "%s" was added.', Mage::helper('core')->escapeHtml($code))
                );
                $result['added'] = true;
                $result['html'] = $this->getLayout()
                    ->createBlock('core/template')
                    ->setTemplate('snap/checkout/item.phtml')
                    ->setData('card', $card)
                    ->toHtml();
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('checkout/session')->addError(
                    $e->getMessage()
                );
                $result['msg'] = $e->getMessage();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot apply gift card.'));
                $result['msg'] = $e->getMessage();
            }
        }
        if ($this->getRequest()->isAjax()) {
            return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        } else {
            $this->_redirect('checkout/cart');
        }
    }

    public function testAction()
    {
        $client = Mage::getModel('snap_card/client');
    }
    
    public function jsHelperAction() {
        $body = "var snapBaseURL = \"" . addslashes(Mage::getBaseUrl()) . "\";";
        
        return $this->getResponse()->setBody($body);
    }
    
    /**
     * Check SNAP giftcard balances.
     */
    public function checkBalanceAction() {
        if (!$this->getRequest()->isAjax()) {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            // This is an AJAX call, return balance
            $result = array(
                "success" => false,
                "error" => "Unknown error."
            );
            try {
                $data = $this->getRequest()->getPost();
                $card_number = $data["snap_card"];
                $pin = $data["snap_card_pin"];
                $valueCode = "USD";
                
                $balance = Mage::helper('snap_card')->getBalance($card_number, $pin, $valueCode);
                if($balance !== false) {
                    $result["success"] = true;
                    $result["balance"] = $balance;
                    $result["valueCode"] = $valueCode;
                    $result["balanceDisp"] = Mage::helper('core')->formatPrice($balance);
                } else {
                    $result["error"] = "The card number you provided was not valid.";
                }
            } catch(Exception $e) {
                Mage::log("Problem!");
                $result["error"] = "Internal server error. Please try again.";
            }
            
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        
    }
    

}
