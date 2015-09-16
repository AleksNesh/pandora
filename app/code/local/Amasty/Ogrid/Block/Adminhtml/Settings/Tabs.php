<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Block_Adminhtml_Settings_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('amogridsettings_tabs');
        $this->setDestElementId('attributesForm');
        $this->setTitle(Mage::helper('amogrid')->__('Columns Configuration'));
    }
    
    protected function _beforeToHtml()
    {
        $this->addTab('attributes_section', array(
            'label'     => Mage::helper('amogrid')->__('Columns Configuration'),
            'title'     => Mage::helper('amogrid')->__('Columns Configuration'),
            'content'   => $this->getLayout()->createBlock('amogrid/adminhtml_settings_tab_main')->toHtml(),
        ));
        
        return parent::_beforeToHtml();
    }
}
?>