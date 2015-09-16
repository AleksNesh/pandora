<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */ 
class Amasty_Shiprestriction_Block_Adminhtml_Rule_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Shiprestriction_Helper_Data */
        $hlp = Mage::helper('amshiprestriction');
    
        $fldStore = $form->addFieldset('apply_in', array('legend'=> $hlp->__('Apply In')));
        $fldStore->addField('for_admin', 'select', array(
          'label'     => $hlp->__('Admin Area'),
          'name'      => 'for_admin',
          'values'    => array(Mage::helper('catalog')->__('No'), Mage::helper('catalog')->__('Yes')),
        ));          
        
        $fldStore->addField('stores', 'multiselect', array(
            'label'     => $hlp->__('Stores'),
            'name'      => 'stores[]',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'note'      => $hlp->__('Leave empty or select all to apply the rule to any'), 
        ));  

        $fldCust = $form->addFieldset('apply_for', array('legend'=> $hlp->__('Apply For')));
        $fldCust->addField('cust_groups', 'multiselect', array(
            'name'      => 'cust_groups[]',
            'label'     => $hlp->__('Customer Groups'),
            'values'    => $hlp->getAllGroups(),
            'note'      => $hlp->__('Leave empty or select all to apply the rule to any group'),
        ));              
        
        //set form values
        $form->setValues(Mage::registry('amshiprestriction_rule')->getData()); 
        
        return parent::_prepareForm();
    }
}