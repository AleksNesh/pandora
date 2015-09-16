<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Conformity_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('conformity_form', array('legend' => Mage::helper('upslabel')->__('Conformity information')));

        $fieldset->addField('method_id', 'select', array(
            'name' => 'method_id',
            'label' => Mage::helper('upslabel')->__('Shipping Method in Checkout'),
            'title' => Mage::helper('upslabel')->__('Shipping Method in Checkout'),
            'required' => true,
            'values' => Mage::getModel('upslabel/config_upsmethod')->getShippingMethods(),
        ));

        $fieldset->addField('upsmethod_id', 'select', array(
            'name' => 'upsmethod_id',
            'label' => Mage::helper('upslabel')->__('UPS Shipping Service for labels'),
            'title' => Mage::helper('upslabel')->__('UPS Shipping Service for labels'),
            'required' => true,
            'values' => Mage::getModel('upslabel/config_upsmethod')->toOptionArray(),
        ));

        $fieldset->addField('country_ids', 'multiselect', array(
            'name' => 'country_ids',
            'label' => Mage::helper('upslabel')->__('Allowed Countries'),
            'title' => Mage::helper('upslabel')->__('Allowed Countries'),
            'required' => true,
            'values' => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
        ));
        
/*
        $fieldset->addField('international', 'select', array(
            'name' => 'international',
            'label' => Mage::helper('upslabel')->__('International'),
            'title' => Mage::helper('upslabel')->__('International'),
            'required' => true,
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
*/
        if (Mage::getSingleton('adminhtml/session')->getAccountData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getAccountData());
            Mage::getSingleton('adminhtml/session')->setAccountData(null);
        } elseif (Mage::registry('conformity_data') && count(Mage::registry('conformity_data')->getData()) > 0) {
            $data = Mage::registry('conformity_data')->getData();
            $form->setValues($data);
        }
        return parent::_prepareForm();
    }
}