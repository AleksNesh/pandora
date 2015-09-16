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
class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Edit_Form_Items_Itemsgrid extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid
{
    protected $_subtotal = null;
    protected $_discount = null;
    protected $_items = array();

    /**
     * Get button to configure product
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return mixed
     */
    public function getConfigureButtonHtml($item)
    {
        $product = $item->getProduct();

        $options = array('label' => Mage::helper('sales')->__('Configure'));
        if ($product->canConfigure()) {
            $options['onclick'] = sprintf('orderEditItems.showQuoteItemConfiguration(%s)', $item->getId());
        } else {
            $options['class'] = ' disabled';
            $options['title'] = Mage::helper('sales')->__('This product does not have any configurable options');
        }

        return $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData($options)
            ->toHtml();
    }

    public function getSubtotal()
    {
        if (is_null($this->_subtotal)) {
            foreach ($this->getItems() as $item) {
                $this->_subtotal += $item->getRowTotal();
            }
        }

        return $this->_subtotal;
    }

    public function getDiscountAmount()
    {
        if (is_null($this->_discount)) {
            foreach ($this->getItems() as $item) {
                if (count($item->getChildren()) > 0) {
                    foreach ($item->getChildren() as $childItem) {
                        $this->_discount += $childItem->getDiscountAmount();
                    }
                }
                $this->_discount += $item->getDiscountAmount();
            }
        }

        return $this->_discount;
    }

    /**
     * Returns the items
     *
     * @return array
     */
    public function getItems()
    {
        if (!empty($this->_items)) {
            return $this->_items;
        }

        $items = $this->getParentBlock()->getItems();
        $oldSuperMode = $this->getQuote()->getIsSuperMode();
        $this->getQuote()->setIsSuperMode(false);

        foreach ($items as $item) {
            if (!$item->getId()) {
                $item->setId($item->getProductId());
            }
            // To dispatch inventory event sales_quote_item_qty_set_after, set item qty
            $qty = floatval($item->getRowTotal()) ? $item->getQty() : 0;
            $item->setQty($qty);
            $stockItem = $item->getProduct()->getStockItem();
            if ($stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                if ($item->getProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                    $item->setMessage(Mage::helper('adminhtml')->__('This product is currently disabled.'));
                    $item->setHasError(true);
                }
            }
        }
        $this->getQuote()->setIsSuperMode($oldSuperMode);

        foreach ($items as $key => $item) {
            /** @var MageWorx_OrdersPro_Model_Edit_Quote $model */
            $model = Mage::getSingleton('mageworx_orderspro/edit_quote');
            if ($model->clearQuoteItems($item, true)) {
                unset($items[$key]);
            }
        }

        $this->_items = $items;
        return $items;
    }

}