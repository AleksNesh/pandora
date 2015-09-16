<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Shiprestriction_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('ruleTabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('amshiprestriction')->__('Rule Configuration'));
    }

    protected function _beforeToHtml()
    {
        $tabs = array(
            'general'    => 'Restrictions',
            'stores'     => 'Stores & Customer Groups',
            'conditions' => 'Conditions',
        );
        
        foreach ($tabs as $code => $label){
            $label = Mage::helper('amshiprestriction')->__($label);
            $content = $this->getLayout()->createBlock('amshiprestriction/adminhtml_rule_edit_tab_' . $code)
                ->setTitle($label)
                ->toHtml();
                
            $this->addTab($code, array(
                'label'     => $label,
                'content'   => $content,
            ));
        }
        
        return parent::_beforeToHtml();
    }
}