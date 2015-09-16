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

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Edit_Wrapper extends Mage_Adminhtml_Block_Template
{
    /**
     * Get blocks of order which can be edited (JSON)
     *
     * @return string
     */
    public function getBlocksJson()
    {
        $blocks = Mage::helper('mageworx_orderspro/edit')->getAvailableBlocks();
        return Zend_Json::encode($blocks);
    }

    /**
     * Get currency symbol for order
     *
     * @return string
     * @throws Exception
     */
    public function getCurrencySymbol()
    {
        $orderId = $this->getRequest()->getParam('order_id', false);
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order || !$order->getId()) {
            return '';
        }

        $currency = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode());
        $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();

        return $symbol;
    }

    /**
     * Preapre html for output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= $this->getLayout()->createBlock('adminhtml/catalog_product_composite_configure')->toHtml();
        return $html;
    }
}