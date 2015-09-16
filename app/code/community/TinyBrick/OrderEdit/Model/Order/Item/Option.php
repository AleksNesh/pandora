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
class TinyBrick_OrderEdit_Model_Order_Item_Option extends Mage_Core_Model_Abstract
{
    /**
     *
     * @var type $_item Item
     * @var type $_product Product 
     */
    protected $_item;
    protected $_product;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('sales/order_item_option');
    }
    /**
     * Sets the item Id
     * @param object $item Item Object
     * @return TinyBrick_OrderEdit_Model_Order_Item_Option 
     */
    public function setItem($item)
    {
        $this->setItemId($item->getId());
        $this->_item = $item;
        return $this;
    }
    /**
     * Gets the item object
     * @return object $_item 
     */
    public function getItem()
    {
        return $this->_item;
    }
    /**
     * Sets the product by ID
     * @param object $product Product Object
     * @return TinyBrick_OrderEdit_Model_Order_Item_Option 
     */
    public function setProduct($product)
    {
        $this->setProductId($product->getId());
        $this->_product = $product;
        return $this;
    }
    /**
     * Gets the product object
     * @return type $_product
     */
    public function getProduct()
    {
        return $this->_product;
    }
    /**
     * Before saving, it sets the item id
     * @return object
     */
    protected function _beforeSave()
    {
        if ($this->getItem()) {
            $this->setItemId($this->getItem()->getId());
        }
        return parent::_beforeSave();
    }
    /**
     * Sets the items to null
     * @return TinyBrick_OrderEdit_Model_Order_Item_Option 
     */
    public function __clone()
    {
        $this->setId(null);
        $this->_item    = null;
        return $this;
    }
}