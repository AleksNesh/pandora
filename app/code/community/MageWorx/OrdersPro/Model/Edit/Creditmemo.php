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

class MageWorx_OrdersPro_Model_Edit_Creditmemo extends Mage_Core_Model_Abstract
{
    /**
     * List of totals which can be changed in order
     * @var array
     */
    protected $_availableTotals = array(
        'shipping_tax_amount',
        'base_shipping_tax_amount',
        'base_shipping_tax_amount',
        'subtotal',
        'base_subtotal',
        'subtotal_incl_tax',
        'base_subtotal_incl_tax',
        'grand_total',
        'base_grand_total',
        'tax_amount',
        'base_tax_amount',
        'discount_amount',
        'base_discount_amount',
        'shipping_amount',
        'base_shipping_amount',
        'shipping_incl_tax',
        'base_shipping_incl_tax',
        'hidden_tax_amount',
        'base_hidden_tax_amount'
    );

    /**
     * Array of order products to be refunded
     *
     * @var array
     */
    protected $_itemsToRefund = array();

    /**
     * Create creditmemo for order changes
     *
     * @param Mage_Sales_Model_Order $origOrder
     * @param Mage_Sales_Model_Order $newOrder
     * @param $changes
     * @return $this
     */
    public function refundChanges(Mage_Sales_Model_Order $origOrder, Mage_Sales_Model_Order $newOrder, $changes)
    {
        $cmData = array();
        $cmData['qtys'] = $this->getItemsToRefund();

        $creditmemo = Mage::getModel('sales/service_order', $newOrder)->prepareCreditmemo($cmData);

        foreach ($this->_availableTotals as $code) {
            $diff = $origOrder->getData($code) - $newOrder->getData($code);
            if (!$diff) {
                continue;
            }

            $creditmemo->setData($code, $diff);
        }

        // Return refunded items to stock
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $creditmemoItem->setBackToStock(true);
        }

        $creditmemo->register();
        $creditmemo->save();

        $creditmemo->getOrder()->save();

        return $this;
    }

    /**
     * Add order item to be refunded
     *
     * @param $itemId
     * @param $qty
     * @return $this
     */
    public function addItemToRefund($itemId, $qty)
    {
        $this->_itemsToRefund[$itemId] = $qty;

        return $this;
    }

    /**
     * Get all the order items to be refunded
     *
     * @return array
     */
    public function getItemsToRefund()
    {
        // To prevent creditmemo from adding items;
        if (!count($this->_itemsToRefund)) {
            return array(0 => 0);
        }

        return $this->_itemsToRefund;
    }
}