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
 * @copyright  Copyright (c) 2014 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
class MageWorx_OrdersPro_Model_Observer
{
    const XML_DAYS = 'mageworx_orderspro/general/days_before_orders_get_archived';

    public function scheduledArchiveOrders($schedule)
    {
        $days = intval(Mage::getStoreConfig(self::XML_DAYS));
        if (!$this->getMwHelper()->isEnabled() || $days == 0) return;
        $archiveOrdersStatus = explode(',', Mage::getStoreConfig('mageworx_orderspro/general/archive_orders_status'));
        $orders = Mage::getResourceModel('mageworx_orderspro/order_collection')->setFilterOrdersNoGroup($days)->addFieldToFilter('status', array('in' => $archiveOrdersStatus));
        $orderIds = array();
        foreach ($orders as $ord) {
            $orderIds[] = $ord->getEntityId();
        }

        // to archive
        $this->getMwHelper()->addToOrderGroup($orderIds, 1);
    }


    // before edit order set old price
    public function convertOrderItemToQuoteItem($observer)
    {
        $helper = $this->getMwHelper();
        if (!$helper->isEnabled()) return $this;

        $quoteItem = $observer->getEvent()->getQuoteItem();

        // fix for magento 1620-1700:
        $shippingAddress = $quoteItem->getQuote()->getShippingAddress();
        if ($shippingAddress) $shippingAddress->setSameAsBilling(0);

        // KeepPurchasePrice
        $orderItem = $observer->getEvent()->getOrderItem();
        $storeId = $orderItem->getOrder()->getStoreId();
        $store = Mage::app()->getStore($storeId);


        $oldQuoteItemId = $orderItem->getQuoteItemId();

        $oldPrice = $orderItem->getPrice();
        if (Mage::helper('tax')->priceIncludesTax($store)) $oldPrice = $orderItem->getOriginalPrice(); // $oldPrice = $orderItem->getPriceInclTax();


        $coreResource = Mage::getSingleton('core/resource');
        $read = $coreResource->getConnection('core_read');

        if ($orderItem->getProductType() != 'bundle' && $oldQuoteItemId > 0) {
            $select = $read->select()
                ->from($coreResource->getTableName('sales_flat_quote_item'), 'original_custom_price')
                ->where('item_id = ?', $oldQuoteItemId);
            $originalCustomPrice = $read->fetchOne($select);
            if ($originalCustomPrice) $oldPrice = $originalCustomPrice;
        }

        if ($orderItem->getProductType() == 'configurable') {
            $productId = $orderItem->getProductId();
            $itemPrice = $quoteItem->getParentItem()->getProduct()->getPriceModel()->getFinalPrice(1, $quoteItem->getParentItem()->getProduct());
            $items = $quoteItem->getQuote()->getItemsCollection();
            foreach ($items as $item) {
                if ($item->getProduct()->getId() == $productId && !$item->getApplyPriceFlag()) {
                    if ($oldPrice != $itemPrice) {
                        $item->setCustomPrice($oldPrice)->setOriginalCustomPrice($oldPrice);
                    }
                    $item->setApplyPriceFlag(true); // mark item
                }
            }
            return $this;
        } elseif ($orderItem->getProductType() == 'bundle') {
            // prepare bundle old price
            if (!$oldQuoteItemId) return $this;
            if ($quoteItem->getParentItem()) $quoteItem = $quoteItem->getParentItem();
            $select = $read->select()
                ->from($coreResource->getTableName('sales_flat_quote_item'), array('product_id', 'price', 'original_custom_price', 'price_incl_tax'))
                ->where('parent_item_id = ?', $oldQuoteItemId);
            $children = $read->fetchAll($select);
            if (!$children) return $this;
            $orderChildren = array();
            foreach ($children as $child) {
                $orderChildren[$child['product_id']] = $child;
            }

            // foreach all children and apply old price
            $children = $quoteItem->getChildren();
            if (!$children) return $this;
            foreach ($children as $child) {
                if (isset($orderChildren[$child->getProductId()])) {
                    $orderChild = $orderChildren[$child->getProductId()];
                    if (Mage::helper('tax')->priceIncludesTax($store)) {
                        $oldPrice = $orderChild['price_incl_tax'];
                    } else {
                        $oldPrice = $orderChild['price'];
                    }
                    $oldPrice = $orderChild['original_custom_price'] ? $orderChild['original_custom_price'] : $oldPrice;
                    if ($oldPrice != $child->getProduct()->getFinalPrice()) $child->setCustomPrice($oldPrice)->setOriginalCustomPrice($oldPrice);
                }
            }
            return $this;
        }

        // simple
        if ($oldPrice != $quoteItem->getProduct()->getFinalPrice()) $quoteItem->setCustomPrice($oldPrice)->setOriginalCustomPrice($oldPrice);

    }


    // before edit order collectShippingRates
    public function convertOrderToQuote($observer)
    {
        $helper = $this->getMwHelper();
        if (!$helper->isEnabled()) return $this;

        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();

        // set same_as_billing = yes/no
        if ($shipping) {
            if ($billing->getFirstname() == $shipping->getFirstname()
                && $billing->getMiddlename() == $shipping->getMiddlename()
                && $billing->getSuffix() == $shipping->getSuffix()
                && $billing->getCompany() == $shipping->getCompany()
                && $billing->getStreet() == $shipping->getStreet()
                && $billing->getCity() == $shipping->getCity()
                && $billing->getRegion() == $shipping->getRegion()
                && $billing->getRegionId() == $shipping->getRegionId()
                && $billing->getPostcode() == $shipping->getPostcode()
                && $billing->getCountryId() == $shipping->getCountryId()
                && $billing->getTelephone() == $shipping->getTelephone()
                && $billing->getFax() == $shipping->getFax()
            ) {
                $shipping->setSameAsBilling(1);
                Mage::getSingleton('adminhtml/sales_order_create')->getShippingAddress()->setSameAsBilling(1);
            } else {
                Mage::getSingleton('adminhtml/sales_order_create')->setShippingAsBilling(0);
            }
        }

        $store = Mage::getSingleton('adminhtml/session_quote')->getStore();
        if (Mage::helper('tax')->shippingPriceIncludesTax($store)) {
            $baseShippingAmount = $order->getBaseShippingInclTax();
        } else {
            $baseShippingAmount = $order->getBaseShippingAmount();
        }
        Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice($baseShippingAmount);

        // for collectShippingRates
        //$quote->setRecollect(true); //$quote->setShippingMethod($order->getShippingMethod());
        $quote->setTotalsCollectedFlag(false);
    }


    public function orderCreateProcessData($observer)
    {
        $request = $observer->getEvent()->getRequest();
        if (isset($request['order']['shipping_price'])) {
            $shippingPrice = $request['order']['shipping_price'];
            if ($shippingPrice == 'null') $shippingPrice = null; else $shippingPrice = floatval($shippingPrice);
            Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice($shippingPrice);
        }
        // if no cancel reset_shipping - recollectShippingRates
        Mage::getSingleton('adminhtml/sales_order_create')->collectShippingRates();
    }

    // edit order set old coupone
    public function quoteCollectTotalsAfter($observer)
    {
        if (!$this->getMwHelper()->isEnabled()) return $this;
        $quote = $observer->getEvent()->getQuote();

        // apply custom shipping price
        if ($this->getMwHelper()->isShippingPriceEditEnabled() && Mage::app()->getStore()->isAdmin()) {
            $address = $quote->getShippingAddress();
            $baseShippingCustomPrice = Mage::getSingleton('adminhtml/session_quote')->getBaseShippingCustomPrice();
            if ($address && !is_null($baseShippingCustomPrice)) {
                if ($address->getShippingMethod()) {

                    $origBaseShippingInclTax = $address->getBaseShippingInclTax();
                    $origShippingInclTax = $address->getShippingInclTax();

                    $address->setBaseTotalAmount('shipping', $baseShippingCustomPrice);
                    $shippingCustomPrice = $quote->getStore()->convertPrice($baseShippingCustomPrice);
                    $address->setTotalAmount('shipping', $shippingCustomPrice);

                    $creditModel = null;
                    $address->setAppliedTaxesReset(false);

                    foreach ($address->getTotalCollector()->getCollectors() as $code => $model) {
                        // for calculate shipping tax
                        if ($code == 'tax_shipping' || $code == 'tax') {
                            $model->collect($address);
                        }
                        if ($code == 'customercredit') $creditModel = $model;
                    }

                    $address->setGrandTotal((float)$address->getGrandTotal() + ($address->getShippingInclTax() - $origShippingInclTax));
                    $address->setBaseGrandTotal((float)$address->getBaseGrandTotal() + ($address->getBaseShippingInclTax() - $origBaseShippingInclTax));

                    // for recollect customer credit and authorizenet in admin
                    if ($creditModel && $address->getBaseCustomerCreditAmount() > 0) {
                        $baseCreditLeft = $address->getBaseCustomerCreditAmount();
                        $creditLeft = $address->getCustomerCreditAmount();
                        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $baseCreditLeft);
                        $address->setGrandTotal($address->getGrandTotal() + $creditLeft);
                        $creditModel->collect($address);
                    }

                } else {
                    Mage::getSingleton('adminhtml/session_quote')->setBaseShippingCustomPrice(null);
                }
            }
        }

        // apply old coupon_code
        $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrderId();
        if (!$orderId) return $this;

        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) return $this;
        if (!$order->getAppliedRuleIds()) return $this;

        if (!$quote->getCouponCode() && !Mage::getSingleton('adminhtml/session_quote')->getCouponCodeIsDeleted() && $order->getCouponCode()) {
            $quote->setCouponCode($order->getCouponCode());

            foreach ($quote->getAllAddresses() as $address) {
                $amount = $address->getDiscountAmount();
                if ($amount != 0) {
                    $description = $order->getDiscountDescription();
                    if ($description) {
                        $title = Mage::helper('sales')->__('Discount (%s)', $description);
                    } else {
                        $title = Mage::helper('sales')->__('Discount');
                    }
                    $address->setCouponCode($order->getCouponCode())->setDiscountDescription($description);
                }
            }
        }

        return $this;
    }

//    public function orderGridCollectionLoadBefore(Varien_Event_Observer $observer) {
//        $collection = $observer->getEvent()->getOrderGridCollection();
//        echo $collection->getSelect(); exit;
//        return $this;
//    }

    /** Add coupon block after order items block
     *  (for order view page)
     *
     * @param Varien_Event_Observer $observer
     */
    public function insertCouponBlock($observer)
    {

        /** @var Varien_Object $transport */
        $transport = $observer->getTransport();
        /** @var Mage_Core_Block_Abstract $block */
        $block = $observer->getBlock();
        if($block->getType() == 'adminhtml/sales_order_view_items' && $block->getNameInLayout() == 'order_items')
        {
            /** @var string $oldHtml */
            $oldHtml = $transport->getHtml();

            /** @var string $couponsBlockHtml */
            $couponsBlockHtml = Mage::getSingleton('core/layout')
                ->createBlock('mageworx_orderspro/adminhtml_sales_order_coupons', 'coupons')
                ->toHtml();

            /** @var string $newHtml */
            $newHtml = $oldHtml . $couponsBlockHtml; // append coupon block html
            $transport->setHtml($newHtml);
        }

        return;
    }

    /**
     * @return MageWorx_OrdersPro_Helper_Data
     */
    protected function getMwHelper()
    {
        return Mage::helper('mageworx_orderspro');
    }
}