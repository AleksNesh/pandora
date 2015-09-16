<?php
/**
 * Giftcard observer
 *
 * @category    Snap
 * @package     Snap_Card
 * @author      alex
 */
class Snap_Card_Model_Observer
{
    /**
     * Increase order snapcards_amount_invoiced attribute based on created invoice
     * used for event: sales_order_invoice_register
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function increaseOrderGiftCardInvoicedAmount(Varien_Event_Observer $observer)
    {
        /*$invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($invoice->getBaseSnapCardsAmount()) {
            $order->setBaseSnapCardsInvoiced($order->getBaseSnapCardsInvoiced() + $invoice->getBaseSnapCardsAmount());
            $order->setSnapCardsInvoiced($order->getSnapCardsInvoiced() + $invoice->getSnapCardsAmount());
        }
        return $this;*/
    }

    /**
     * Set the flag that we need to collect overall totals
     *
     * @param Varien_Event_Observer $observer
     */
    public function quoteCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        //$quote = $observer->getEvent()->getQuote();
        //$quote->setSnapCardsTotalCollected(false);
    }

    /**
     * Process card update after it was used
     * @param Varien_Event_Observer $observer
     */
    public function updateCardOnPlaceOrder(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        /*$order = $observer->getOrder();

        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        Mage::log("Update card on place order.");*/
    }
    
    
    /**
     * Place giftcard holds if needed. (Giftcard authorize step)
     * @param Varien_Event_Observer $observer
     */
    public function salesEventConvertQuoteToOrder(Varien_Event_Observer $observer) {
        Mage::log("salesEventConvertQuoteToOrder");
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cards = Mage::helper('snap_card')->getCards($quote);
        if(sizeof($cards) > 0) {
            Mage::log("Need to place holds for SNAP giftcards, giftcard count: " . sizeof($cards));
            
            $success = true;
            foreach($cards as $i => $card) {
                Mage::log("Placing Hold transaction for card: " . $card["c"] . ", amount: " . $card["a"]);
                $decryptedPin = Mage::helper('snap_card')->decryptPin($card["pin"]);
                $transaction_id = Mage::helper('snap_card')->holdBalance($card["c"], $decryptedPin, $card["a"], "USD");
                $cards[$i]["transaction_id"] = $transaction_id;
                Mage::log("Hold transaction result: " . ($transaction_id !== false ? "Success" : "Failure"));
                if($transaction_id === false) {
                    $success = false;
                    break;
                }
            }
            
            if(!$success) {
                Mage::throwException(Mage::helper("snap_card")->__('Your giftcard balance has become unavailable during the payment processing. Please contact customer support.'));
            }
            
            Mage::helper('snap_card')->setCards($quote, $cards);
            $quote->save();
        }
        
        return $this;
    }
    
    /**
     * Place giftcard hold redemptions if needed. (Giftcard capture step)
     * @param Varien_Event_Observer $observer
     */
    public function checkoutSubmitAllAfter(Varien_Event_Observer $observer) {
        Mage::log("checkoutSubmitAllAfter");
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $cards = Mage::helper('snap_card')->getCards($quote);
        if(sizeof($cards) > 0) {
            Mage::log("Need to redeem holds for SNAP giftcards, giftcard count: " . sizeof($cards));
            
            $success = true;
            foreach($cards as $card) {
                Mage::log("Redeeming Hold transaction for card: " . $card["c"] . ", amount: " . $card["a"] . ", hold transaction ID: " . $card["transaction_id"]);
                $decryptedPin = Mage::helper('snap_card')->decryptPin($card["pin"]);
                $success = Mage::helper('snap_card')->holdRedemption($card["c"], $decryptedPin, $card["a"], "USD", $card["transaction_id"]);
                Mage::log("Hold redemption transaction result: " . ($success ? "Success" : "Failure"));
                if(!$success) {
                    break;
                }
            }
            
            if($success) {
                $quoteId = Mage::getSingleton("checkout/session")->getQuoteId();
                $order = $observer->getEvent()->getOrder();
                $orderId = $order->getId();
                $incrementId = $order->getIncrementId();
                
                Mage::log("Quote ID is now: " . $quoteId . ", order ID is: " . $orderId . ", increment ID: " . $incrementId);
                Mage::helper('snap_card')->attachOrderIdToCards($orderId);
            } else {
                Mage::throwException(Mage::helper("snap_card")->__('Your giftcard balance could not be redeemed. Please contact customer support.'));
            }
        }
        
        return $this;
    }
    
    /**
     * Cancel an order afterwards
     * @param Varien_Event_Observer $observer
     */
    public function orderCancelAfter(Varien_Event_Observer $observer) {
        Mage::log("orderCancelAfter");
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();
        Mage::log("Cancelling order: " . $orderId);
        
        Mage::helper('snap_card')->fullReturn($orderId);
    }
    
    /**
     * Save an order after it has already been placed - usually for state changes.
     * @param Varien_Event_Observer $observer
     */
    public function salesOrderStatusAfter(Varien_Event_Observer $observer) {
        Mage::log("salesOrderStatusAfter");
        $order = $observer->getEvent()->getOrder();
        $state = $order->getState();
        Mage::log("State: " . $state);
        Mage::log("Obj: " . print_r($order, true));
    }
    
    public function salesOrderInvoiceCancel(Varien_Event_Observer $observer) {
        Mage::log("salesOrderInvoiceCancel");
        $order = $observer->getEvent()->getOrder();
        $state = $order->getState();
        Mage::log("State: " . $state);
        Mage::log("Obj: " . print_r($order, true));
    }
}
