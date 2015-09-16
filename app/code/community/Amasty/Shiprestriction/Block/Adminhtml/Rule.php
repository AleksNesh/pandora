<?php
/**
 * @author Amasty
 */   
class Amasty_Shiprestriction_Block_Adminhtml_Rule extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rule';
        $this->_blockGroup = 'amshiprestriction';
        $this->_headerText = Mage::helper('amshiprestriction')->__('Rules');
        $this->_addButtonLabel = Mage::helper('amshiprestriction')->__('Add Rule');
        parent::__construct();
    }
}