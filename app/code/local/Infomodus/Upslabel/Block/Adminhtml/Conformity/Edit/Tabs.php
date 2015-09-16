<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Conformity_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('conformity_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('upslabel')->__('UPS conformity information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('upslabel')->__('Conformity Information'),
            'title'     => Mage::helper('upslabel')->__('Conformity Information'),
            'content'   => $this->getLayout()->createBlock('upslabel/adminhtml_conformity_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}