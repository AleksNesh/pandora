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
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
class MageWorx_OrdersPro_Block_Adminhtml_Customer_Edit_Tab_Orders extends Mage_Adminhtml_Block_Customer_Edit_Tab_Orders
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultFilter(array('order_group' => 0)); // Actual
    }

    /**
     * Prepare columns for orders grid in customer account (Admin side)
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $helper = Mage::helper('mageworx_orderspro');
        if (!$helper->isEnabled()) return parent::_prepareColumns();

        $listColumns = $helper->getCustomerGridColumns();
        //$currencyCode = $helper->getCurrentCurrencyCode();

        foreach ($listColumns as $column) {
            switch ($column) {

                // standard fields                
                case 'increment_id':
                    $this->addColumn('increment_id', array(
                        'header' => Mage::helper('customer')->__('Order #'),
                        'width' => '80px',
                        'type' => 'text',
                        'index' => 'increment_id',
                    ));
                    break;

                case 'created_at':
                    $this->addColumn('created_at', array(
                        'header' => Mage::helper('customer')->__('Purchase On'),
                        'index' => 'created_at',
                        'type' => 'datetime',
                        'width' => '100px',
                    ));
                    break;

                case 'billing_name':
                    $this->addColumn('billing_name', array(
                        'header' => Mage::helper('customer')->__('Bill to Name'),
                        'index' => 'billing_name',
                    ));
                    break;

                case 'shipping_name':
                    $this->addColumn('shipping_name', array(
                        'header' => Mage::helper('customer')->__('Shiped to Name'),
                        'index' => 'shipping_name',
                    ));
                    break;

                case 'grand_total':
                    $this->addColumn('grand_total', array(
                        'header' => Mage::helper('customer')->__('Order Total'),
                        'index' => 'grand_total',
                        'type' => 'currency',
                        'currency' => 'order_currency_code',
                    ));
                    break;

                case 'store_id':
                    if (!Mage::app()->isSingleStoreMode()) {
                        $this->addColumn('store_id', array(
                            'header' => Mage::helper('customer')->__('Bought From'),
                            'index' => 'store_id',
                            'type' => 'store',
                            'store_view' => true
                        ));
                    }
                    break;

                case 'action':
                    $this->addColumn('action', array(
                        'header' => ' ',
                        'filter' => false,
                        'sortable' => false,
                        'width' => '100px',
                        'renderer' => 'adminhtml/sales_reorder_renderer_action'
                    ));

                    break;

                // additional fields

                case 'product_names':
                    $this->addColumn('product_names', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_products',
                        'header' => $helper->__('Product Name(s)') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'index' => 'product_names'
                    ));
                    break;

                case 'product_skus':
                    $this->addColumn('product_skus', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_products',
                        'header' => $helper->__('SKU(s)'),
                        'index' => 'skus'
                    ));
                    break;

                case 'product_options':
                    $this->addColumn('product_options', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_products',
                        'header' => $helper->__('Product Option(s)'),
                        'index' => 'product_options',
                        'filter' => false,
                        'sortable' => false
                    ));
                    break;

                case 'customer_email':
                    $this->addColumn('customer_email', array(
                        'type' => 'text',
                        'header' => $helper->__('Customer Email'),
                        'index' => 'customer_email'
                    ));
                    break;


                case 'customer_group':
                    $this->addColumn('customer_group', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_registry',
                        'type' => 'options',
                        'options' => $helper->getCustomerGroups(),
                        'header' => $helper->__('Customer Group'),
                        'index' => 'customer_group_id',
                        'align' => 'center'
                    ));
                    break;


                case 'payment_method':
                    $this->addColumn('payment_method', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_registry',
                        'type' => 'options',
                        'options' => $helper->getAllPaymentMethods(),
                        'header' => $helper->__('Payment Method'),
                        'index' => 'method',
                        'align' => 'center'
                    ));
                    break;

                case 'base_total_refunded':
                    $this->addColumn('base_total_refunded', array(
                        'type' => 'currency',
                        'currency' => 'base_currency_code',
                        'header' => $helper->__('Total Refunded (Base)'),
                        'index' => 'base_total_refunded',
                        'total' => 'sum'
                    ));
                    break;
                case 'total_refunded':
                    $this->addColumn('total_refunded', array(
                        'type' => 'currency',
                        'currency' => 'order_currency_code',
                        'header' => $helper->__('Total Refunded (Purchased)'),
                        'index' => 'total_refunded',
                        'total' => 'sum'
                    ));
                    break;

                case 'shipping_method':
                    $this->addColumn('shipping_method', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_registry',
                        'type' => 'options',
                        'options' => $helper->getAllShippingMethods(),
                        'filter' => 'mageworx_orderspro/adminhtml_sales_order_grid_filter_shipping',
                        'header' => $helper->__('Shipping Method'),
                        'index' => 'shipping_method',
                        'align' => 'center'
                    ));
                    break;

                case 'tracking_number':
                    $this->addColumn('tracking_number', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_street',
                        'type' => 'text',
                        'header' => $helper->__('Tracking Number'),
                        'index' => 'tracking_number'
                    ));
                    break;

                case 'shipped':
                    $this->addColumn('shipped', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_registry',
                        'type' => 'options',
                        'options' => $helper->getShippedStatuses(),
                        'header' => $helper->__('Shipped'),
                        'index' => 'shipped',
                        'align' => 'center'
                    ));
                    break;

                case 'order_group':
                    $this->addColumn('order_group', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_registry',
                        'type' => 'options',
                        'options' => $helper->getOrderGroups(),
                        'header' => $helper->__('Group'),
                        'index' => 'order_group_id',
                        'align' => 'center',
                    ));
                    break;


                case 'qnty':
                    $this->addColumn('qnty', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_qnty',
                        'filter' => false,
                        'sortable' => false,
                        'header' => $helper->__('Qnty'),
                        'index' => 'total_qty',
                    ));
                    break;

                case 'weight':
                    $this->addColumn('weight', array(
                        'type' => 'number',
                        'header' => $helper->__('Weight'),
                        'index' => 'weight',
                    ));
                    break;

                case 'base_tax_amount':
                    $this->addColumn('base_tax_amount', array(
                        'type' => 'currency',
                        'currency' => 'base_currency_code',
                        'header' => $helper->__('Tax Amount (Base)'),
                        'index' => 'base_tax_amount'
                    ));
                    break;
                case 'tax_amount':
                    $this->addColumn('tax_amount', array(
                        'type' => 'currency',
                        'currency' => 'order_currency_code',
                        'header' => $helper->__('Tax Amount (Purchased)'),
                        'index' => 'tax_amount'
                    ));
                    break;

                case 'base_discount_amount':
                    $this->addColumn('base_discount_amount', array(
                        'type' => 'currency',
                        'currency' => 'base_currency_code',
                        'header' => $helper->__('Discount (Base)'),
                        'index' => 'base_discount_amount'
                    ));
                    break;
                case 'discount_amount':
                    $this->addColumn('discount_amount', array(
                        'type' => 'currency',
                        'currency' => 'order_currency_code',
                        'header' => $helper->__('Discount (Purchased)'),
                        'index' => 'discount_amount'
                    ));
                    break;

                case 'base_internal_credit':
                    if (Mage::getConfig()->getModuleConfig('MageWorx_CustomerCredit')->is('active', true)) {
                        $this->addColumn('base_internal_credit', array(
                            'type' => 'currency',
                            'currency' => 'base_currency_code',
                            'header' => $helper->__('Internal Credit (Base)'),
                            'index' => 'base_customer_credit_amount'
                        ));
                    }
                    break;
                case 'internal_credit':
                    if (Mage::getConfig()->getModuleConfig('MageWorx_CustomerCredit')->is('active', true)) {
                        $this->addColumn('internal_credit', array(
                            'type' => 'currency',
                            'currency' => 'order_currency_code',
                            'header' => $helper->__('Internal Credit (Purchased)'),
                            'index' => 'customer_credit_amount'
                        ));
                    }
                    break;

                case 'coupon_code':
                    $this->addColumn('coupon_code', array(
                        'type' => 'text',
                        'header' => $helper->__('Coupon Code'),
                        'align' => 'center',
                        'index' => 'coupon_code'
                    ));
                    break;


                case 'is_edited':
                    $this->addColumn('is_edited', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_registry',
                        'type' => 'options',
                        'options' => $helper->getEditedStatuses(),
                        'header' => $helper->__('Edited'),
                        'index' => 'is_edited',
                        'align' => 'center'
                    ));
                    break;

                case 'status':
                    $this->addColumn('status', array(
                        'header' => Mage::helper('sales')->__('Status'),
                        'index' => 'status',
                        'type' => 'options',
                        'width' => '70px',
                        'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
                    ));
                    break;

                case 'order_comment':
                    $this->addColumn('order_comment', array(
                        'renderer' => 'mageworx_orderspro/adminhtml_sales_order_grid_renderer_comments',
                        'header' => $helper->__('Order Comment(s)'),
                        'index' => 'order_comment'
                    ));
                    break;

            }
        }

        $this->sortColumnsByOrder();
        return $this;
    }

}
