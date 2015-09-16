<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Table_Block_Adminhtml_Method_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('methodTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amtable')->__('Method Methods'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'general'    => 'General',
            'stores'     => 'Stores & Customer Groups',
            'import'     => 'Import',
        );
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('amtable')->__($label);
            $content = $this->getLayout()->createBlock('amtable/adminhtml_method_edit_tab_' . $code)
                ->setTitle($label)
                ->toHtml();
                
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
            ));
        }
        
        $this->addTab('rates', array(
            'label'     => Mage::helper('amtable')->__('Methods and Rates'),
            'class'     => 'ajax',
            'url'       => $this->getUrl('amtable/adminhtml_rate/index', array('_current' => true)),
        ));
    
        
        $this->_updateActiveTab();    
    
        return parent::_beforeToHtml();
    }
    
    protected function _updateActiveTab()
    {
    	$tabId = $this->getRequest()->getParam('tab');
    	if ($tabId) {
    		$tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
    		if ($tabId) {
    			$this->setActiveTab($tabId);
    		}
    	}
    	else {
    	   $this->setActiveTab('main'); 
    	}
    }     
 
    
}