<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Table_Block_Adminhtml_Method_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; 
        $this->_blockGroup = 'amtable';
        $this->_controller = 'adminhtml_method';
        
        $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('amtable')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 10);
       
        $mid = Mage::registry('amtable_method')->getId();    
        if ($mid) {
            $this->_addButton('new', array(
                    'label' => Mage::helper('amtable')->__('Add New Rate'),
                    'onclick' => 'newRate()',
                    'class' => 'add'
                ),15);

            $url = $this->getUrl('*/adminhtml_rate/edit', array('mid'=>$mid));  
            $this->_formScripts[] = " function newRate(){ setLocation('$url'); } ";    
        }    
        $this->_formScripts[] = " function saveAndContinueEdit(){ editForm.submit($('edit_form').action + 'continue/edit') }";       
    }

    public function getHeaderText()
    {
        $header = Mage::helper('amtable')->__('New Method');
        $model = Mage::registry('amtable_method');
        if ($model->getId()){
            $header = Mage::helper('amtable')->__('Edit Method `%s`', $model->getName());
        }
        return $header;
    }
}