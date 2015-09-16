<?php
/**
 * Card totals renderer
 *
 * @category   Snap
 * @package    Snap_Card
 * @author     alex
 */

class Snap_Card_Block_Checkout_Cart_Total extends Mage_Checkout_Block_Total_Default
{
    protected $_template = 'snap/cart/total.phtml';

    protected function _getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getQuoteGiftCards()
    {
        return Mage::helper('snap_card')->getCards($this->_getQuote());
    }
}
