<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */  
class Amasty_Table_Block_Adminhtml_Method_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Table_Helper_Data */
        $hlp = Mage::helper('amtable');
    
        $fldStore = $form->addFieldset('apply_in', array('legend'=> $hlp->__('Visible In')));
        $fldStore->addField('stores', 'multiselect', array(
            'label'     => $hlp->__('Stores'),
            'name'      => 'stores[]',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'note'      => $hlp->__('Leave empty if there are no restrictions'), 
        ));  

        $fldCust = $form->addFieldset('apply_for', array('legend'=> $hlp->__('Applicable For')));
        $fldCust->addField('cust_groups', 'multiselect', array(
            'name'      => 'cust_groups[]',
            'label'     => $hlp->__('Customer Groups'),
            'values'    => $hlp->getAllGroups(),
            'note'      => $hlp->__('Leave empty if there are no restrictions'),
        ));              
        
        //set form values
        $form->setValues(Mage::registry('amtable_method')->getData()); 
        
        return parent::_prepareForm();
    }
}