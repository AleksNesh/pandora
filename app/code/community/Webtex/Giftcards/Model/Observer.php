<?php

class Webtex_Giftcards_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Process saving gift card product
     *
     * @param $observer
     */
    public function catalogProductSaveBefore($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == 'giftcards') {
            $product->setRequiredOptions('1');
        }
    }

    /**
     * Process saving order after user place order
     * Creates gift cards and charge off discount amount (only cards part) from user's balance
     *
     * @param $observer
     */
    public function checkoutTypeOnepageSaveOrderAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            $orders = $observer->getEvent()->getOrders();
            $order = array_shift($orders);
        }

        $quote = $observer->getEvent()->getQuote();

        if ($quote) {
            try {
                /* Create cards if its present in order */
                foreach ($quote->getAllVisibleItems() as $item) {
                    if ($item->getProduct()->getTypeId() == 'giftcards') {
                        $options = $item->getProduct()->getCustomOptions();
                        $optionsDataMap = array(
                            'card_type',
                            'mail_to',
                            'mail_to_email',
                            'mail_from',
                            'mail_message',
                            'offline_country',
                            'offline_state',
                            'offline_city',
                            'offline_street',
                            'offline_zip',
                            'offline_phone',
                            'mail_delivery_date',
                            'card_currency'
                        );
                        $data = array();
                        foreach ($optionsDataMap as $field) {
                            if (isset($options[$field])) {
                                $data[$field] = $options[$field]->getValue();
                            }
                        }
                        $data['card_amount'] = $item->getCalculationPrice()+$item->getTaxAmount();

                        $data['product_id'] = $item->getProductId();
                        $data['card_status'] = 0;
                        $data['order_id'] = $order->getId();

                        $curDate = date('m/d/Y');
                        for ($i = 0; $i < $item->getQty(); $i++) {
                            $model = Mage::getModel('giftcards/giftcards');
                            $model->setData($data);
                            if (in_array($order->getState(), array('complete'))) {
                                $model->setCardStatus(1);
                                $model->save();

                                if ((($curDate == $data['mail_delivery_date']) || empty($data['mail_delivery_date'])) && $data['card_type'] != 'offline') {
                                    $model->send();
                                }
                            } else {
                                $model->setCardStatus(0);
                                $model->save();
                            }
                        }
                    }
                }

                if ($quote->getUseGiftcards()) {
                    $giftCardsIds = $quote->getGiftCardsIds();

                    $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                        ->addFieldToFilter('card_status', 1)
                        ->addFieldToFilter('card_id', array(
                            'in' => $giftCardsIds
                        ));

                    $value = $quote->getGiftcardsDiscount();


                    $baseCurrency = $quote->getBaseCurrencyCode();

                    foreach ($cards as $card) {
                        $oGiftCardOrder = Mage::getModel('giftcards/order');

                        if (is_null($card->getCardCurrency()) || $card->getCardCurrency() == $baseCurrency) {
                            $useAmount = min($card->getCardBalance(), $value);
                            if ($useAmount > 0) {
                                $newCardBalance = $card->getCardBalance() - $useAmount;
                                $card->setCardBalance($newCardBalance);
                                if ($newCardBalance == 0) {
                                    $card->setCardStatus(2); //set status to 'used' when gift card balance is 0;
                                }
                                $card->save();

                                $oGiftCardOrder->setIdGiftcard($card->getId());
                                $oGiftCardOrder->setIdOrder($order->getId());
                                $oGiftCardOrder->setDiscounted((float)$useAmount);
                                $oGiftCardOrder->save();

                                if ($value - $useAmount > 0) {
                                    $value -= $useAmount;
                                } else {
                                    //if got more than 2 giftcards & after discount of 1st cart(etc) order amount is 0,
                                    //don't need to continue calculation & update data
                                    break;
                                }
                            }
                        } else {
                            $convertedCardBalance = Mage::helper('giftcards')->currencyConvert($card->getCardBalance(), /*from*/
                                $card->getCardCurrency(), /*to*/
                                $baseCurrency);
                            $useAmount = min($convertedCardBalance, $value);
                            if ($useAmount > 0) {
                                $tempBase = $convertedCardBalance - $useAmount;
                                $newCardBalance = Mage::helper('giftcards')->currencyConvert($tempBase, $baseCurrency, $card->getCardCurrency());
                                $card->setCardBalance($newCardBalance);
                                if ($newCardBalance == 0) {
                                    $card->setCardStatus(2); //set status to 'used' when gift card balance is 0;
                                }
                                $card->save();

                                $oGiftCardOrder->setIdGiftcard($card->getId());
                                $oGiftCardOrder->setIdOrder($order->getId());
                                //$useAmount = Mage::helper('giftcards')->currencyConvert($useAmount, $baseCurrency, $card->getCardCurrency()); store data in gift card order in base currency
                                $oGiftCardOrder->setDiscounted((float)$useAmount);
                                $oGiftCardOrder->save();

                                if ($value - $useAmount > 0) {
                                    $value -= $useAmount;
                                } else {
                                    //if got more than 2 giftcards & after discount of 1st cart(etc) order amount is 0,
                                    //don't need to continue calculation & update data
                                    break;
                                }
                            }
                        }
                    }
                }
                Mage::getSingleton('giftcards/session')->clear();
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($order, $e->getMessage());
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
            }
        }
    }

    /**
     * Process order cancel
     * Adds discounted amount back to user's balance (whole part?)
     *
     * @param $observer
     */
    public function salesOrderCancelAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $giftCardsOrderCollection = Mage::getModel('giftcards/order')->getCollection()
            ->addFieldToFilter('id_order', $order->getId());

        if ($giftCardsOrderCollection->getSize() > 0) {
            $giftCardsIds = array();
            $discounted = array();
            foreach ($giftCardsOrderCollection as $giftCardOrderItem) {
                $giftCardsIds[] = $giftCardOrderItem->getIdGiftcard();
                $discounted[$giftCardOrderItem->getIdGiftcard()] = $giftCardOrderItem->getDiscounted();
            }
            $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                ->addFieldToFilter('card_id', $giftCardsIds);
            foreach ($cards as $card) {
                if (is_null($card->getCardCurrency()) || $card->getCardCurrency() == $order->getBaseCurrencyCode()) {
                    $card->setCardBalance($card->getCardBalance() + $discounted[$card->getId()]);
                } else {
                    $reddemedValue = Mage::helper('giftcards')->currencyConvert($discounted[$card->getId()], $order->getBaseCurrencyCode(), $card->getCardCurrency());
                    $card->setCardBalance($card->getCardBalance() + $reddemedValue);
                }

                $card->setCardStatus(1);
                $card->save();

                $oGiftCardOrder = Mage::getModel('giftcards/order');
                $oGiftCardOrder->setIdGiftcard($card->getId());
                $oGiftCardOrder->setIdOrder($order->getId());
                $oGiftCardOrder->setDiscounted(-(float)$discounted[$card->getId()]);
                $oGiftCardOrder->save();
            }
        }
    }


    /**
     * Process order refund
     * Adds discounted amount back to user's balance
     *
     * @param $observer
     */
    public function saleOrderPaymentRefund($observer)
    {
        $oCreditmemo = $observer['creditmemo'];
        $oOrder = $oCreditmemo->getOrder();
        $giftCardsOrderCollection = Mage::getModel('giftcards/order')->getCollection()
            ->addFieldToFilter('id_order', $oOrder->getId());

        if ($giftCardsOrderCollection->getSize() > 0) {
            $gcAmountDiscount = 0;
            foreach ($giftCardsOrderCollection as $giftCardOrderItem) {
                $giftCardsIds[] = $giftCardOrderItem->getIdGiftcard();
                $gcAmountDiscount += $giftCardOrderItem->getDiscounted();
                $aDiscounted[$giftCardOrderItem->getIdGiftcard()] = $giftCardOrderItem->getDiscounted();
            }

            //calculate shipping refund
            $baseShippingRefunded = $oOrder->getBaseShippingRefunded();
            $baseShippingDiscountAmount = $oOrder->getBaseShippingDiscountAmount();

            if ($baseShippingRefunded != $baseShippingDiscountAmount) {
                $reduceBalance = $baseShippingDiscountAmount - $baseShippingRefunded;
                //just add balance to gift cards
                foreach ($aDiscounted as &$v) {
                    if ($v >= $reduceBalance) {
                        $v -= $reduceBalance;
                        $reduceBalance = 0;
                    } else {
                        $reduceBalance -= $v;
                    }
                }
            }

            $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                ->addFieldToFilter('card_id', $giftCardsIds);

            foreach ($cards as $card) {
                if (is_null($card->getCardCurrency()) || $card->getCardCurrency() == $oOrder->getBaseCurrencyCode()) {
                    $card->setCardBalance($card->getCardBalance() + $aDiscounted[$card->getId()]);
                } else {
                    $reddemedValue = Mage::helper('giftcards')->currencyConvert($aDiscounted[$card->getId()], $oOrder->getBaseCurrencyCode(), $card->getCardCurrency());
                    $card->setCardBalance($card->getCardBalance() + $reddemedValue);
                }

                $card->setCardStatus(1);
                $card->save();

                $oGiftCardOrder = Mage::getModel('giftcards/order');
                $oGiftCardOrder->setIdGiftcard($card->getId());
                $oGiftCardOrder->setIdOrder($oOrder->getId());
                $oGiftCardOrder->setDiscounted(-(float)$aDiscounted[$card->getId()]);
                $oGiftCardOrder->save();
            }
        }
    }

    /**
     * Process order saving
     * Send cards emails on order complete
     *
     * @param $observer
     */
    public function salesOrderSaveAfter($observer)
    {
        $curDate = date('Y-m-d');
        $order = $observer->getEvent()->getOrder();
        if (in_array($order->getState(), array('complete'))) {
            $cards = Mage::getModel('giftcards/giftcards')->getCollection()
                ->addFieldToFilter('order_id', $order->getId());
            foreach ($cards as $card) {
              if($card->getCardStatus() == 0) {
                $card->setCardStatus(1)->save();
                if ((($card->getMailDeliveryDate() == null) || ($curDate >= $card->getMailDeliveryDate())) && $card->getCardType() != 'offline') {
                    $card->send();
                }
              }
            }
        }
    }

    /**
     * Hide price for giftcard in product list when price of giftcard product isn't defined(=0)
     * @param $observer
     */
    public function checkPriceIsZero($observer)
    {
        $block = $observer->getBlock();

        if (get_class($block) === 'Mage_Catalog_Block_Product_Price') {
            $product = $block->getProduct();
            if ($product->getTypeId() === 'giftcards') {
                if ($product->getPrice() == 0) {
                    $observer->getTransport()->setHtml('&nbsp');
                }
            }
        }
    }

    /**
     * Send email based on delivery date specified by customer
     * starts every day at 01.00 am (see config.xml)
     */
    public function sendEmailByDeliveryDate()
    {
        $currentDate = date('Y-m-d');
        $oGiftCards = Mage::getModel('giftcards/giftcards')->getCollection()
            ->addFieldToFilter('mail_delivery_date', array('eq' => $currentDate))
            ->addFieldToFilter('card_status', 1);
        foreach ($oGiftCards as $oGiftCard) {
            $oGiftCard->send();
        }
    }
}