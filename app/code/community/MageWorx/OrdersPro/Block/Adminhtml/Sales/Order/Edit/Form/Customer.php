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

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Edit_Form_Customer extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form to edit customer info in order
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addField('customer_id', 'hidden', array(
                'name'  => 'customer_id',
                'label' => Mage::helper('adminhtml')->__('Customer ID'),
                'title' => Mage::helper('adminhtml')->__('Customer ID'),
                'required' => false,
                'readonly' => true,
                'after_element_html' => '<span id="current_customer_id">'.$this->getOrder()->getCustomerId().'</span><a href="#" class="change-customer" onclick="orderEdit.changeCustomer()">'.$this->__('Change').'</a>'
            )
        );

        $form->addField('customer_firstname', 'text', array(
                'name'  => 'customer_firstname',
                'label' => Mage::helper('adminhtml')->__('First Name'),
                'title' => Mage::helper('adminhtml')->__('First Name'),
                'required' => true,
            )
        );

        $form->addField('customer_lastname', 'text', array(
                'name'  => 'customer_lastname',
                'label' => Mage::helper('adminhtml')->__('Last Name'),
                'title' => Mage::helper('adminhtml')->__('Last Name'),
                'required' => true,
            )
        );

        $form->addField('customer_email', 'text', array(
                'name'  => 'customer_email',
                'label' => Mage::helper('adminhtml')->__('Customer Email'),
                'title' => Mage::helper('adminhtml')->__('Customer Email'),
                'required' => true,
            )
        );

        $customerGroups = Mage::getSingleton('adminhtml/system_config_source_customer_group')->toOptionArray();
        $form->addField('customer_group_id', 'select', array(
                'name'  => 'customer_group_id',
                'label' => Mage::helper('adminhtml')->__('Customer Group'),
                'title' => Mage::helper('adminhtml')->__('Customer Group'),
                'required' => true,
                'values' => $customerGroups
            )
        );

        $form->setValues($this->getOrder()->getData());

        $form->setUseContainer(true);
        $form->setId('orderspro_edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}