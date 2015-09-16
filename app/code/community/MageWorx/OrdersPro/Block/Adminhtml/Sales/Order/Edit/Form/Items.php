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

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Edit_Form_Items extends Mage_Adminhtml_Block_Sales_Order_Create_Items
{
    /**
     * Preapre layout to show "edit order items" form
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $grid = $this->getLayout()->createBlock('mageworx_orderspro/adminhtml_sales_order_edit_form_items_itemsgrid')->setTemplate('mageworx/orderspro/edit/items/grid.phtml');
        $this->append($grid);

        return $this;
    }

    public function getQuote()
    {
        $order = $this->getOrder() ? $this->getOrder() : Mage::registry('orderspro_order');
        $quote = Mage::getSingleton('mageworx_orderspro/edit')->getQuoteByOrder($order);

        return $quote;
    }

    protected function _toHtml()
    {
        $html = $this->getChildHtml();
        $html .= '<div id="orderspro_product_grid"></div>';

        //Configure existing order items
        //$html .= $this->getLayout()->createBlock('adminhtml/catalog_product_composite_configure')->toHtml();

        return $html;
    }
}