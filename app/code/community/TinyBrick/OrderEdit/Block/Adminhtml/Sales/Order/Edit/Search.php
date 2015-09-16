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
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_Edit_Search extends TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_Edit_Abstract 
{
    /**
     * Constructs the search grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_edit_search');
    }
    /**
     * Gets the header text
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Please Select Products to Add');
    }
    /**
     * Gets the buttons for the grid
     * @return object
     */
    public function getButtonsHtml()
    {
        $addButtonData = array(
            'label' => Mage::helper('sales')->__('Add Selected Product(s) to Order555'),
            'onclick' => 'order.productGridAddSelected()',
            'class' => 'add',
        );
        return $this->getLayout()->createBlock('adminhtml/widget_button')->setData($addButtonData)->toHtml();
    }
    /**
     * gets and returns the header css class for the grid
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-catalog-product';
    }

}