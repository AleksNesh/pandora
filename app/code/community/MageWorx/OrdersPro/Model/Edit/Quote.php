<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Model_Edit_Quote extends Mage_Core_Model_Abstract
{
    protected $_orderItems = array();

    /**
     * Apply all the changes to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param array $data
     * @return Mage_Sales_Model_Quote
     */
    public function applyDataToQuote(Mage_Sales_Model_Quote $quote, array $data)
    {
        foreach ($data as $key => $value) {
            if ($key == 'shipping_address') {
                $this->setAddress($quote, $value, 'shipping');
            } elseif ($key == 'billing_address') {
                $this->setAddress($quote, $value, 'billing');
            } elseif ($key == 'payment') {
                $this->setPayment($quote, $value);
            } elseif ($key == 'shipping') {
                $this->setShipping($quote, $value);
            } elseif ($key == 'quote_items') {
                $this->updateItems($quote, $value);
            } elseif ($key == 'product_to_add') {
                $this->addNewItems($quote, $value);
            } elseif ($key == 'coupon_code') {
                $this->setCouponCode($quote, $value);
            }
        }

        // Clear quote from canceled items
        $this->clearQuote($quote);

        // If multifees enabled
        $this->collectMultifees();

        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->saveTemporaryItems($quote, 1, true);

        if (isset($data['coupon_code'])) 
        {
            $this->validateCouponCode($quote, $data['coupon_code']);
        }

        return $quote;
    }

    /**
     * Apply shipping/billing address to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param $data
     * @param $addressType
     * @return $this
     */
    public function setAddress(Mage_Sales_Model_Quote $quote, $data, $addressType)
    {
        $address = ($addressType == 'shipping') ? $quote->getShippingAddress() : $quote->getBillingAddress();
        $address->addData($data);

        // fix for street fields
        $streetArray = array();
        for ($i = 0; $i < 4; $i++) {
            if (isset($data['street[' . $i])) {
                $streetArray[$i] = $data['street[' . $i];
            }
        }
        $street = implode(chr(10), $streetArray);
        $streetData = array('street' => $street);
        $address->addData($streetData);
        // fix end

        return $this;
    }

    /**
     * Apply payment method to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param $data
     * @return $this
     */
    public function setPayment(Mage_Sales_Model_Quote $quote, $data)
    {
        $payment = $quote->getPayment();
        $payment->addData($data);

        return $this;
    }

    /**
     * Apply shipping method data to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param $data
     * @return $this
     */
    public function setShipping(Mage_Sales_Model_Quote $quote, $data)
    {
        $address = $quote->getShippingAddress();

        if (isset($data['custom_price'])) {
            Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice($data['custom_price']);
        } else {
            Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice(null);
        }

        if (isset($data['shipping_method'])) {
            $address->setShippingMethod($data['shipping_method']);
        }

        return $this;
    }

    /**
     * Apply updated order items to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param $data
     * @return $this
     */
    public function updateItems(Mage_Sales_Model_Quote $quote, $data)
    {
        foreach ($data as $itemId => $params) {
            $quoteItem = $quote->getItemById($itemId);
            if (!$quoteItem) {
                $this->addNewItems($quote, array($itemId => $params));
                $quoteItem = $quote->getItemById($itemId);
                if (!$quoteItem) {
                    continue;
                }
            }

            if ((isset($params['action']) && $params['action'] == 'remove')
                || ((isset($params['qty']) && $params['qty'] < 1))
            ) {
                $quote->removeItem($itemId);
                continue;
            }

            if (isset($params['qty'])) {
                $quoteItem->setQty($params['qty']);
            }

            if (isset($params['custom_price']) && $params['custom_price'] > 0) {
                $quoteItem->setCustomPrice((float)$params['custom_price']);
                $quoteItem->setOriginalCustomPrice((float)$params['custom_price']);
            }

            $noDiscount = !isset($params['use_discount']);
            $quoteItem->setNoDiscount($noDiscount);

            $quoteItem->save();
        }

        return $this;
    }

    /**
     * Apply newly added products to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param $data
     * @return $this
     */
    public function addNewItems(Mage_Sales_Model_Quote $quote, $data)
    {
        foreach ($data as $productId => $params) {

            $product = Mage::getModel('catalog/product')->setStoreId($quote->getStoreId())->load($productId);
            if (!$product || !$product->getId()) {
                continue;
            }

//            if (!$quote->getItemByProduct($product))
//            {
                if (!isset($params['product']))
                {
                    $params['product'] = $product->getId();
                }

                $newQuoteItem = $quote->addProduct($product, new Varien_Object($params));
            /** @var Mage_Sales_Model_Quote_Item $item */
            //$item = $quote->getItemByProduct($product);
//            } else {
//                $item = $quote->getItemByProduct($product);
//                if (isset($params['qty'])) {
//                    $qty = $item->getQty();
//                    $item->setQty($params['qty']);
//                }
//                $item->save();
//            }
        }

        if (Mage::registry('orderspro_order'))
        {
            Mage::helper('mageworx_orderspro/edit')->addPendingChanges(Mage::registry('orderspro_order')->getId(), array(
                    'product_to_add' => array()
                )
            );
        }

        return $this;
    }

    /**
     * Set new coupon code to quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param                        $couponCode string | null
     *
     * @return $this
     */
    public function setCouponCode($quote, $couponCode = '')
    {
        $quote->setCouponCode($couponCode);

        return $this;
    }

    /**
     * Validate current coupon code
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param string                 $couponCode
     *
     * @return bool
     */
    protected function validateCouponCode($quote, $couponCode = '')
    {
        $codeLength = strlen($couponCode);
        $isCodeLengthValid = $codeLength && $codeLength <= 255;

        // Validate NEW coupon
        if ($codeLength) {
            if ($isCodeLengthValid && $couponCode == $quote->getCouponCode()) {
                Mage::getSingleton('adminhtml/session')->setCouponMessage(
                    Mage::helper('checkout/cart')
                        ->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                );

                return true;
            } else {
                // If NEW coupon is not valid add error message
                Mage::getSingleton('adminhtml/session')->setCouponMessage(
                    Mage::helper('checkout/cart')
                        ->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                );
                Mage::helper('mageworx_orderspro/edit')->addPendingChanges(Mage::registry('orderspro_order')->getEntityId(),
                    array('coupon_code' => ''));

                return false; // reset coupon code to empty
            }
        } else {
            Mage::getSingleton('adminhtml/session')->setCouponMessage(Mage::helper('checkout/cart')
                ->__('Coupon code was canceled.'));
            $quote->setCouponCode('');

            return true;
        }
    }

    /**
     * Save temp items in quote with "is_temporary" flag
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param int                    $flag
     * @param bool                   $checkItemId
     */
    public function saveTemporaryItems(Mage_Sales_Model_Quote $quote, $flag = 0, $checkItemId = false)
    {
        foreach ($quote->getAllVisibleItems() as $item)
        {
            if ($item->getId() && $checkItemId)
            {
                continue;
            }
            $item->setData('orderspro_is_temporary', $flag)->save();
        }
    }

    protected function collectMultifees()
    {
        if ((string)Mage::getConfig()->getModuleConfig('MageWorx_MultiFees')->active != 'true')
        {
            return;
        }

        $order = Mage::registry('orderspro_order');
        if ($order)
        {
            $feesPost = $this->convertOrdersToFeeSubmitPost($order->getDetailsMultifees());
            Mage::helper('multifees')->addFeesToCart($feesPost, $order->getStoreId(), true, 0, 0);
        }
    }

    protected function convertOrdersToFeeSubmitPost($feesData) {
        if ($feesData) $feesData = unserialize($feesData); else $feesData = array();
        foreach ($feesData as $feeId => $data) {
            if (!isset($data['options'])) continue;
            foreach ($data['options'] as $optionId=>$value) {
                $feesData[$feeId]['options'][$optionId] = $optionId;
            }
        }
        return $feesData;
    }

    protected function clearQuote(Mage_Sales_Model_Quote $quote)
    {
        $items = $quote->getAllItems();

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($items as $item)
        {
            if ($this->clearQuoteItems($item, false))
            {
                $quote->removeItem($item->getId());
            }
        }
    }

    /** Return true for non valid items
     *
     * @param      $item
     * @param bool $checkChild
     * @return bool
     */
    public function clearQuoteItems($item, $checkChild = false)
    {
        if ($item->getParentItem() && $checkChild) {
            return true;
        }

        $orderItem = $this->getOrderItemByQuoteItem($item);
        if ($orderItem && ($orderItem->getQtyToCancel() + $orderItem->getQtyToRefund()) < 0.001 && $orderItem->getQtyOrdered() == $item->getQty()) {
            return true;
        }

        return false;
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     * @return Mage_Sales_Model_Order_Item | null
     */
    protected function getOrderItemByQuoteItem(Mage_Sales_Model_Quote_Item $item)
    {
        if (empty($this->_orderItems)) {
            $order = Mage::registry('orderspro_order');
            if ($order instanceof Mage_Sales_Model_Order) {
                $orderItemsCollection = $order->getItemsCollection(array(), true);
                $this->_orderItems = $orderItemsCollection->getItems();
            }
        }

        foreach ($this->_orderItems as $orderItem) {
            if ($orderItem->getQuoteItemId() == $item->getItemId()) {
                return $orderItem;
            }
        }

        return null;
    }
}