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
 * @copyright  Copyright (c) 2011 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
class MageWorx_OrdersPro_Model_Edit extends Mage_Core_Model_Abstract
{
    /**
     * Order items which have already been saved
     *
     * @var array
     */
    protected $_savedOrderItems = array();

    /**
     * The flag shows whether missed quote items have been checked
     *
     * @var bool
     */
    protected $_quoteItemsAlreadyChecked = false;

    /**
     * Get model for logging order changes
     *
     * @return MageWorx_OrdersPro_Model_Edit_Log
     */
    public function getLogModel()
    {
        return Mage::getSingleton('mageworx_orderspro/edit_log');
    }

    /**
     * Get quote model by order
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean|Mage_Sales_Model_Quote
     */
    public function getQuoteByOrder(Mage_Sales_Model_Order $order)
    {
        $quoteId = $order->getQuoteId();
        $storeId = $order->getStoreId();

        $this->checkQuoteItems($quoteId, $order);

        $quote = Mage::getModel('sales/quote')->setStoreId($storeId)->load($quoteId);

        return $quote;
    }

    /**
     * Check and restore quote items which have been deleted from database
     *
     * @param                        $quoteId
     * @param Mage_Sales_Model_Order $order
     * @internal param \Mage_Sales_Model_Quote $quote
     * @return $this
     */
    public function checkQuoteItems($quoteId, Mage_Sales_Model_Order $order)
    {
        if ($this->_quoteItemsAlreadyChecked) {
            return $this;
        }

        foreach ($order->getAllItems() as $orderItem) {
            $quoteItemId = $orderItem->getQuoteItemId();
            $quoteItem = Mage::getModel('sales/quote_item')->load($quoteItemId);

            if ($quoteItem && $quoteItem->getId()) {
                continue;
            }

            $product = Mage::getModel('catalog/product')
                ->setStoreId($order->getStoreId())
                ->load($orderItem->getProductId());

            $newQuoteItem = Mage::getModel('sales/convert_order')->itemToQuoteItem($orderItem);
            $newQuoteItem->setQuoteId($quoteId)
                ->setProduct($product)
                ->save();

            $orderItem->setQuoteItemId($newQuoteItem->getItemId())->save();
        }

        $this->_quoteItemsAlreadyChecked = true;

        return $this;
    }

    /**
     * Remove specific qty of order item from order
     *
     * @param Mage_Sales_Model_Order      $order
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param null                        $qtyToReturn
     * @return $this
     */
    public function returnOrderItem(
        Mage_Sales_Model_Order $order,
        Mage_Sales_Model_Order_Item $orderItem,
        $qtyToReturn = null
    ) {
        if (is_null($qtyToReturn)) {
            $qtyToReturn = $orderItem->getQtyToRefund() + $orderItem->getQtyToCancel();
        }

        if ($qtyToReturn > 0 && $orderItem->getQtyToCancel() > 0) {

            $qtyToCancel = min($qtyToReturn, $orderItem->getQtyToCancel());
            $qtyToReturn -= $qtyToCancel;

            $this->cancelOrderItem($orderItem, $qtyToCancel);
        }

        if ($qtyToReturn > 0 && $orderItem->getQtyToRefund() > 0) {
            $creditMemo = $this->refundOrderItem($order, $orderItem, $qtyToReturn);
        }

        return $this;
    }

    /**
     * Refund specific qty of order item
     *
     * @param Mage_Sales_Model_Order      $order
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param                             $qtyToRefund
     * @return $this
     */
    public function refundOrderItem(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Item $orderItem, $qtyToRefund)
    {
        $cmModel = Mage::getSingleton('mageworx_orderspro/edit_creditmemo');
        $cmModel->addItemToRefund($orderItem->getId(), $qtyToRefund);

        if ($orderItem->getProductType() == 'bundle') {
            $orderItem->setQtyRefunded($qtyToRefund);
        }

        return $this;
    }

    /**
     * Cancel specific qty of order item
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @param null                        $qtyToCancel
     * @return Mage_Sales_Model_Order_Item
     */
    public function cancelOrderItem(Mage_Sales_Model_Order_Item $orderItem, $qtyToCancel = null)
    {
        if ($orderItem->getStatusId() !== Mage_Sales_Model_Order_Item::STATUS_CANCELED) {
            if (is_null($qtyToCancel)) {
                $qtyToCancel = $orderItem->getQtyToCancel();
            }

            Mage::dispatchEvent('sales_order_item_cancel', array('item' => $orderItem));
            $orderItem->setQtyCanceled($orderItem->getQtyCanceled() + $qtyToCancel);
            $orderItem->setTaxCanceled(
                $orderItem->getTaxCanceled() +
                $orderItem->getBaseTaxAmount() * $orderItem->getQtyCanceled() / $orderItem->getQtyOrdered()
            );
            $orderItem->setHiddenTaxCanceled(
                $orderItem->getHiddenTaxCanceled() +
                $orderItem->getHiddenTaxAmount() * $orderItem->getQtyCanceled() / $orderItem->getQtyOrdered()
            );
        }

        return $orderItem;
    }

    /**
     * Invoice order items
     *
     * @param       $order
     * @param array $qtys
     * @return mixed
     */
    public function invoiceOrderItems($order, $qtys = array())
    {
        $invoice = Mage::helper('mageworx_orderspro')->invoiceOrder($order);
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getProductType() == 'bundle') {
                $orderItem->setQtyInvoiced($orderItem->getQtyOrdered() - $orderItem->getQtyCanceled());
                $orderItem->save();
            }
        }

        return $invoice;
    }

    /**
     * Get model for converting quote parts to order
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getConvertor()
    {
        return Mage::getSingleton('mageworx_orderspro/edit_quote_convert');
    }

    /**
     * Save billng/shipping address
     *
     * @param Mage_Sales_Model_Quote_Address $quoteAddress
     * @param Mage_Sales_Model_Order_Address $orderAddress
     * @return $this
     * @throws Exception
     */
    public function saveAddress(
        Mage_Sales_Model_Quote_Address $quoteAddress,
        Mage_Sales_Model_Order_Address $orderAddress
    ) {
        $quote = $quoteAddress->getQuote();
        $order = $orderAddress->getOrder();

        $this->getConvertor()->addressToOrderAddress($quoteAddress, $orderAddress);

        if (($quote->getIsVirtual() && $orderAddress->getAddressType() == 'billing')
            || (!$quote->getIsVirtual() && $orderAddress->getAddressType() == 'shipping')
        ) {
            Mage::helper('core')->copyFieldset('sales_convert_quote_address', 'to_order', $quoteAddress, $order);
        }

        $orderAddress->save();
        $quoteAddress->save();

        return $this;
    }

    /**
     * Save payment method
     *
     * @param Mage_Sales_Model_Quote_Payment $quotePayment
     * @param Mage_Sales_Model_Order_Payment $orderPayment
     * @return $this
     * @throws Exception
     */
    public function savePayment(
        Mage_Sales_Model_Quote_Payment $quotePayment,
        Mage_Sales_Model_Order_Payment $orderPayment
    ) {
        $orderPayment = $this->getConvertor()->paymentToOrderPayment($quotePayment, $orderPayment);
        $orderPayment->save();
        $quotePayment->save();

        return $this;
    }

    /**
     * Save changed order products
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @param                        $changes
     * @return $this
     */
    public function saveOldOrderItems(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order, $changes)
    {
        foreach ($changes as $itemId => $params) {
            $quoteItem = $quote->getItemById($itemId);
            $orderItem = $order->getItemByQuoteItemId($itemId);

            if (!$orderItem) {
                continue;
            }

            $qtyToRefund = $orderItem->getQtyToRefund();
            if ($orderItem->getProductType() == 'bundle') {
                $qtyToRefund = $orderItem->getQtyInvoiced() - $orderItem->getQtyRefunded();
            }
            $orderItemQty = $qtyToRefund + $orderItem->getQtyToCancel();

            if ((isset($params['action']) && $params['action'] == 'remove')
                || (isset($params['qty']) && $params['qty'] < 1)
            ) {

                $this->returnOrderItem($order, $orderItem);

                $orderItem->setSubtotal(0)
                    ->setBaseSubtotal(0)
                    ->setTaxAmount(0)
                    ->setBaseTaxAmount(0)
                    ->setTaxPercent(0)
                    ->setDiscountAmount(0)
                    ->setBaseDiscountAmount(0)
                    ->setRowTotal(0)
                    ->setBaseRowTotal(0);

                continue;
            }

            $origQtyOrdered = $orderItem->getQtyOrdered();
            $orderItem = $this->getConvertor()->itemToOrderItem($quoteItem, $orderItem);

            if (isset($params['qty']) && $params['qty'] != $orderItemQty) {

                $qtyDiff = $params['qty'] - $orderItemQty;

                if ($params['qty'] < $orderItemQty) {
                    $qtyToRemove = $orderItemQty - $params['qty'];
                    $this->returnOrderItem($order, $orderItem, $qtyToRemove);
                    $orderItem->setQtyOrdered($origQtyOrdered);
                } else {
                    $orderItem->setQtyOrdered($origQtyOrdered + $qtyDiff);
                }

                if ($orderItem->getProductType() == 'bundle') {

                    foreach ($quote->getAllItems() as $childQuoteItem) {
                        if ($childQuoteItem->getParentItemId() != $quoteItem->getId()) {
                            continue;
                        }

                        $childQuoteItem->save();

                        $childOrderItem = $order->getItemByQuoteItemId($childQuoteItem->getId());
                        $childOrderItem->setParentItem($orderItem);
                        $origChildQtyOrdered = $childOrderItem->getQtyOrdered();

                        $childOrderItem = $this->getConvertor()->itemToOrderItem($childQuoteItem, $childOrderItem);
//                        Mage::helper('core')->copyFieldset('sales_convert_quote_item', 'to_order_item', $childQuoteItem, $childOrderItem);

                        if ($params['qty'] < $orderItemQty) {

                            $qtyToRemove = $origChildQtyOrdered - $childOrderItem->getQtyOrdered() - $childOrderItem->getQtyCanceled() - $childOrderItem->getQtyRefunded();
                            $this->returnOrderItem($order, $childOrderItem, $qtyToRemove);

                            $childOrderItem->setQtyOrdered($origChildQtyOrdered);
                        } else {
                            $childQtyDiff = $qtyDiff * $childQuoteItem->getQty();
                            $childOrderItem->setQtyOrdered($origChildQtyOrdered + $childQtyDiff);
                        }

                        $childOrderItem->save();

                        $this->_savedOrderItems[] = $childOrderItem->getItemId();
                    }
                }
            }

            // Check Qty & Price changes
            $itemChange = array(
                'name'         => $orderItem->getName(),
                'qty_before'   => $orderItemQty,
                'qty_after'    => $orderItem->getQtyToRefund() + $orderItem->getQtyToCancel(),
                'price_before' => $orderItem->getOrigData('price'),
                'price_after'  => $orderItem->getPrice()
            );

            // Check Discount changes
            if (isset($params['use_discount']) && $params['use_discount'] == 1 && $quoteItem->getOrigData('discount_amount') == 0 && $quoteItem->getData('discount_amount') > 0) {
                $itemChange['discount'] = 1;
            } elseif ($quoteItem->getData('discount_amount') < 0.001 && $quoteItem->getOrigData('discount_amount') > 0) {
                $itemChange['discount'] = -1;
            }

            // Add item changes to log
            if ($itemChange['qty_before'] != $itemChange['qty_after'] || $itemChange['price_before'] != $itemChange['price_after'] || isset($itemChange['discount'])) {
                $this->getLogModel()->addItemChange($orderItem->getId(), $itemChange);
            }

            $quoteItem->save();
            $orderItem->save();

            $this->_savedOrderItems[] = $orderItem->getItemId();
        }

        return $this;
    }

    /**
     * Add new products to order
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @param                        $changes
     * @return $this
     */
    public function saveNewOrderItems(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order, $changes)
    {
        foreach ($quote->getAllItems() as $quoteItem) {
            $orderItem = $order->getItemByQuoteItemId($quoteItem->getItemId());
            if ($orderItem && $orderItem->getItemId()) {
                continue;
            }

            $quoteItem->save();

            $orderItem = $this->getConvertor()->itemToOrderItem($quoteItem, $orderItem);
            $order->addItem($orderItem);
            $orderItem->save();

            /*** Add new items to log ***/
            $changedItem = $quoteItem;
            $itemChange = array(
                'name'       => $changedItem->getName(),
                'qty_before' => 0,
                'qty_after'  => $changedItem->getQty()
            );
            $this->getLogModel()->addItemChange($changedItem->getId(), $itemChange);

            $this->_savedOrderItems[] = $orderItem->getItemId();
        }

        foreach ($quote->getAllItems() as $childQuoteItem) {
            $parentOrderItem = false;
            $childOrderItem = $order->getItemByQuoteItemId($childQuoteItem->getItemId());

            /*** Add items relations for configurable and bundle products ***/
            if ($childQuoteItem->getParentItemId()) {
                $parentOrderItem = $order->getItemByQuoteItemId($childQuoteItem->getParentItemId());

                $childOrderItem->setParentItemId($parentOrderItem->getItemId());
                $childOrderItem->save();
            }

//            /*** Add new items to log ***/
//            $changedItem = $parentOrderItem ? $parentOrderItem : $childOrderItem;
//            $itemChange = array(
//                'name' => $changedItem->getName(),
//                'qty_before' => 0,
//                'qty_after' => $changedItem->getQtyToRefund() + $changedItem->getQtyToCancel()
//            );
//            $this->getLogModel()->addItemChange($changedItem->getId(), $itemChange);
        }

        return $this;
    }

    /**
     * Apply all the changes to order and save it
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order $order
     * @param                        $changes
     * @return $this
     * @throws Exception
     */
    public function saveOrder(Mage_Sales_Model_Quote $quote, Mage_Sales_Model_Order $order, $changes)
    {
        if (isset($changes['billing_address'])) {
            $this->saveAddress($quote->getBillingAddress(), $order->getBillingAddress());
            unset($changes['billing_address']);
        }

        if (isset($changes['shipping_address'])) {
            $this->saveAddress($quote->getShippingAddress(), $order->getShippingAddress());
            unset($changes['shipping_address']);
        }

        if (isset($changes['payment'])) {
            $this->savePayment($quote->getPayment(), $order->getPayment());
            unset($changes['payment']);
        }

        $this->_savedOrderItems = array();

        if (isset($changes['quote_items'])) {
            $this->saveOldOrderItems($quote, $order, $changes['quote_items']);
        }

        if (isset($changes['product_to_add'])) {
            $this->saveNewOrderItems($quote, $order, $changes['product_to_add']);
        }

        $address = $quote->getIsVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        Mage::helper('core')->copyFieldset('sales_convert_quote_address', 'to_order', $address, $order);
        $address->save();

        foreach ($quote->getAllVisibleItems() as $quoteItem) {

            $orderItem = $order->getItemByQuoteItemId($quoteItem->getItemId());

            if (in_array($orderItem->getItemId(), $this->_savedOrderItems)) {
                continue;
            }

            $orderItem = $this->getConvertor()->itemToOrderItem($quoteItem, $orderItem);
            $orderItem->save();
        }

        if (empty($changes['customer_id'])) {
            $changes['customer_id'] = $order->getCustomerId();
        }

        // Collect order all items qty
        $changes['total_qty_ordered'] = 0;
        foreach ($order->getAllItems() as $orderItem) {
            $changes['total_qty_ordered'] += $orderItem['qty_ordered'] - $orderItem['qty_canceled'];
        }
        $order->addData($changes);

        $this->getLogModel()->commitOrderChanges($order);

        $quote->save();
        $order->save();

        return $this;
    }
}