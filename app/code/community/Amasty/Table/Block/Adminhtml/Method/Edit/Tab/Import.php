<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Table_Block_Adminhtml_Method_Edit_Tab_Import extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        //create form structure
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        $hlp = Mage::helper('amtable');
        
        $fldSet = $form->addFieldset('amtable_import', array('legend'=> $hlp->__('Import Rates')));
        $fldSet->addField('import_clear', 'select', array(
          'label'     => $hlp->__('Delete Existing Rates'),
          'name'      => 'import_clear',
          'values'    => array(
            array(
                'value' => 0,
                'label' => Mage::helper('catalog')->__('No')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('catalog')->__('Yes')
            ))
        ));
        $fldSet->addField('import_file', 'file', array(
          'label'     => $hlp->__('CSV File'),
          'name'      => 'import_file',
          'note'      => $hlp->__('Example file http://amasty.com/examples/tablerates.csv')
        ));               

        return parent::_prepareForm();
    }
}