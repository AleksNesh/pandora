<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Model_Resource_Order_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Constructs the SQL
     */
    public function _construct()
    {
        $this->_init('orderedit/order_item');
    }

    /**
     * after loading the SQL, returns the parent information
     * @return TinyBrick_OrderEdit_Model_Resource_Order_Item_Collection 
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        /**
         * Assign parent items
         */
        foreach ($this as $item) {
        	if ($item->getParentItemId()) {
        	    $item->setParentItem($this->getItemById($item->getParentItemId()));
        	}
        }
        return $this;
    }

    /**
     * Set filter by order id
     *
     * @param   mixed $order
     * @return  Mage_Sales_Model_Mysql4_Order_Item_Collection
     */
    public function setOrderFilter($order)
    {
        if ($order instanceof Delorum_QuickOrderEdit_Model_Order) {
            $orderId = $order->getId();
        }
        else {
            $orderId = $order;
        }
        $this->addFieldToFilter('order_id', $orderId);
        return $this;
    }
    /**
     * Sets random order of sql
     * @return TinyBrick_OrderEdit_Model_Resource_Order_Item_Collection 
     */
    public function setRandomOrder()
    {
        $this->setOrder('RAND()');
        return $this;
    }

    /**
     * Set filter by item id
     *
     * @param mixed $item
     * @return Mage_Sales_Model_Mysql4_Order_Item_Collection
     */
    public function addIdFilter($item)
    {
        if (is_array($item)) {
            $this->addFieldToFilter('item_id', array('in'=>$item));
        } elseif ($item instanceof Delorum_QuickOrderEdit_Model_Order_Item) {
            $this->addFieldToFilter('item_id', $item->getId());
        } else {
            $this->addFieldToFilter('item_id', $item);
        }
        return $this;
    }

    /**
     * Filter collection by specified product types
     *
     * @param array $typeIds
     * @return Mage_Sales_Model_Mysql4_Order_Item_Collection
     */
    public function filterByTypes($typeIds)
    {
        $this->addFieldToFilter('product_type', array('in' => $typeIds));
        return $this;
    }

    /**
     * Filter collection by parent_item_id
     *
     * @param int $parentId
     * @return Mage_Sales_Model_Mysql4_Order_Item_Collection
     */
    public function filterByParent($parentId = null)
    {
        if (empty($parentId)) {
            $this->addFieldToFilter('parent_item_id', array('null' => true));
        }
        else {
            $this->addFieldToFilter('parent_item_id', $parentId);
        }
        return $this;
    }
}