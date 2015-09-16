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

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Edit_Form_Address extends Mage_Adminhtml_Block_Sales_Order_Address_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mageworx/orderspro/edit/address.phtml');
    }

    /**
     * Get shipping/billing address to edit
     *
     * @return mixed
     */
    protected function _getAddress()
    {
        $order = $this->getOrder();
        $quote = Mage::getSingleton('mageworx_orderspro/edit')->getQuoteByOrder($order);

        $blockId = Mage::app()->getRequest()->getParam('block_id');
        if ($blockId == 'billing_address') {
            return $quote->getBillingAddress();
        } else {
            return $quote->getShippingAddress();
        }
    }

    /**
     * Get customer who placed the order
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getCustomer()
    {
        $customerId = $this->getOrder()->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);

        return $customer;
    }

    /**
     * Prepare form to edit billing/shipping address
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        // Set custom renderer for VAT field if needed
        $vatIdElement = $this->_form->getElement('vat_id');
        if ($vatIdElement && $this->getDisplayVatValidationButton() !== false) {
            $vatIdElement->setRenderer(
                $this->getLayout()->createBlock('mageworx_orderspro/adminhtml_sales_order_edit_form_address_vat')
                    ->setJsVariablePrefix($this->getJsVariablePrefix())
            );
        }

        $this->_form->setId('orderspro_edit_form');

        return $this;
    }
}