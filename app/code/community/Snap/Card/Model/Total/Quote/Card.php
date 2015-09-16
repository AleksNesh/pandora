<?php
class Snap_Card_Model_Total_Quote_Card extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Init total model, set total code
     */
    public function __construct()
    {
        $this->setCode('snap_card');
    }

    /**
     * Collect giftcertificate totals for specified address
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this|\Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)

    {
        $this->_collectQuoteSnapCards($address->getQuote());
        $baseAmountLeft = $address->getQuote()->getBaseSnapCardsAmount()
            - $address->getQuote()->getBaseSnapCardsAmountUsed();
        $amountLeft = $address->getQuote()->getSnapCardsAmount()-$address->getQuote()->getSnapCardsAmountUsed();

        $baseTotalUsed = $totalUsed = $baseUsed = $used = $skipped = $baseSaved = $saved = 0;

        if ($baseAmountLeft >= $address->getBaseGrandTotal()) {
            $baseUsed = $address->getBaseGrandTotal();
            $used = $address->getGrandTotal();

            $address->setBaseGrandTotal(0);
            $address->setGrandTotal(0);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $address->setBaseGrandTotal($address->getBaseGrandTotal()-$baseAmountLeft);
            $address->setGrandTotal($address->getGrandTotal()-$amountLeft);
        }

        $addressCards = array();
        $usedAddressCards = array();
        if ($baseUsed) {
            $quoteCards = $this->_sortSnapCards(Mage::helper('snap_card')->getCards($address->getQuote()));
            foreach ($quoteCards as $quoteCard) {
                $card = $quoteCard;
                if ($quoteCard['ba'] + $skipped <= $address->getQuote()->getBaseSnapCardsAmountUsed()) {
                    $baseThisCardUsedAmount = $thisCardUsedAmount = 0;
                } elseif ($quoteCard['ba'] + $baseSaved > $baseUsed) {
                    $baseThisCardUsedAmount = min($quoteCard['ba'], $baseUsed-$baseSaved);
                    $thisCardUsedAmount = min($quoteCard['a'], $used-$saved);

                    $baseSaved += $baseThisCardUsedAmount;
                    $saved += $thisCardUsedAmount;
                } elseif ($quoteCard['ba'] + $skipped + $baseSaved > $address->getQuote()->getBaseSnapCardsAmountUsed()) {
                    $baseThisCardUsedAmount = min($quoteCard['ba'], $baseUsed);
                    $thisCardUsedAmount = min($quoteCard['a'], $used);

                    $baseSaved += $baseThisCardUsedAmount;
                    $saved += $thisCardUsedAmount;
                } else {
                    $baseThisCardUsedAmount = $thisCardUsedAmount = 0;
                }
                // avoid possible errors in future comparisons
                $card['ba'] = round($baseThisCardUsedAmount, 4);
                $card['a'] = round($thisCardUsedAmount, 4);
                $addressCards[] = $card;
                if ($baseThisCardUsedAmount) {
                    $usedAddressCards[] = $card;
                }

                $skipped += $quoteCard['ba'];
            }
        }
        Mage::helper('snap_card')->setCards($address, $usedAddressCards);
        $address->setUsedSnapCards($address->getSnapCards());
        Mage::helper('snap_card')->setCards($address, $addressCards);

        $baseTotalUsed = $address->getQuote()->getBaseSnapCardsAmountUsed() + $baseUsed;
        $totalUsed = $address->getQuote()->getSnapCardsAmountUsed() + $used;

        $address->getQuote()->setBaseSnapCardsAmountUsed($baseTotalUsed);
        $address->getQuote()->setSnapCardsAmountUsed($totalUsed);

        $address->setBaseSnapCardsAmount($baseUsed);
        $address->setSnapCardsAmount($used);

        return $this;
    }

    protected function _collectQuoteSnapCards($quote)
    {
        if (!$quote->getSnapCardsTotalCollected()) {
            $quote->setBaseSnapCardsAmount(0);
            $quote->setSnapCardsAmount(0);

            $quote->setBaseSnapCardsAmountUsed(0);
            $quote->setSnapCardsAmountUsed(0);

            $baseAmount = 0;
            $amount = 0;
            $cards = Mage::helper('snap_card')->getCards($quote);
            foreach ($cards as $k=>&$card) {

                /*$model = Mage::getModel('snap_card/giftcard')->load($card['i']);
                if ($model->getBalance() == 0) {
                    unset($cards[$k]);
                } else {
                    $card['a'] = $quote->getStore()->roundPrice($quote->getStore()->convertPrice($card['ba']));
                    $baseAmount += $card['ba'];
                    $amount += $card['a'];
                }*/
                $card['a'] = $quote->getStore()->roundPrice($quote->getStore()->convertPrice($card['ba']));
                $baseAmount += $card['ba'];
                $amount += $card['a'];
            }
            Mage::helper('snap_card')->setCards($quote, $cards);

            $quote->setBaseSnapCardsAmount($baseAmount);
            $quote->setSnapCardsAmount($amount);

            $quote->setSnapCardsTotalCollected(true);
        }
    }

    /**
     * Return shopping cart total row items
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if ($address->getQuote()->isVirtual()) {
            $snapCards = Mage::helper('snap_card')->getCards($address->getQuote()->getBillingAddress());
        } else {
            $snapCards = Mage::helper('snap_card')->getCards($address);
        }
        $address->addTotal(array(
            'code'=>$this->getCode(),
            'title'=>Mage::helper('snap_card')->__('Gift Cards'),
            'value'=> $address->getSnapCardsAmount(),
            'snap_cards'=>$snapCards,
        ));

        return $this;
    }

    protected function _sortSnapCards($in)
    {
        usort($in, array($this, 'compareSnapCards'));
        return $in;
    }

    public static function compareSnapCards($a, $b)
    {
        if ($a['ba'] == $b['ba']) {
            return 0;
        }
        return ($a['ba'] > $b['ba']) ? 1 : -1;
    }
}
