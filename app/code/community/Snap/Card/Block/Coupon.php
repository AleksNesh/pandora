<?php
/**
 * Coupon page Gift Card block
 *
 * @category   Snap
 * @package    Snap_Card
 * @author     alex
 */
class Snap_Card_Block_Coupon extends Mage_Core_Block_Template
{
    /**
     * Get submit gift card url (for cart page)
     *
     * @return string
     */
    public function getApplyUrl()
    {
        return $this->getUrl('giftcard/index/add');
    }

    /**
     * Get applied gift card code
     *
     * @return string|null
     */
    public function getGiftCardCode()
    {
        return $this->getQuote()->getCouponCode();
    }

    /**
     * Get active quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }
}
