<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Table_Block_Adminhtml_Method_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Table_Helper_Data */
        $hlp = Mage::helper('amtable');
    
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Name'),
            'required'  => true,
            'name'      => 'name',
        ));
        $fldInfo->addField('is_active', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Status'),
            'name'      => 'is_active',
            'options'    => $hlp->getStatuses(),
        ));  
            
       
        $fldInfo->addField('pos', 'text', array(
            'label'     => Mage::helper('salesrule')->__('Priority'), 
            'name'      => 'pos',
        ));
        
        //set form values
        $form->setValues(Mage::registry('amtable_method')->getData()); 
        
        return parent::_prepareForm();
    }
}