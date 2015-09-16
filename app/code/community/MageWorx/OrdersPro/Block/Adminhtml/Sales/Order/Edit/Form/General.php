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

class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Edit_Form_General extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Preapre form to edit general info of order
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->addField('created_at', 'date', array(
                'name'  => 'created_at',
                'label' => Mage::helper('adminhtml')->__('Order Date'),
                'title' => Mage::helper('adminhtml')->__('Order Date'),
                'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
                'required' => true,
                'image'              => $this->getSkinUrl('images/grid-cal.gif'),
            )
        );

        $statuses = Mage::getSingleton('adminhtml/system_config_source_order_status')->toOptionArray();
        $form->addField('status', 'select', array(
                'name'  => 'status',
                'label' => Mage::helper('adminhtml')->__('Order Status'),
                'title' => Mage::helper('adminhtml')->__('Order Status'),
                'required' => true,
                'values' => $statuses
            )
        );

        $data = $this->getOrder()->getData();
        $createdAt = Mage::getModel('core/date')->timestamp($data['created_at']);
        $data['created_at'] = $createdAt;

        $form->setValues($data);

        $form->setUseContainer(true);
        $form->setId('orderspro_edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}