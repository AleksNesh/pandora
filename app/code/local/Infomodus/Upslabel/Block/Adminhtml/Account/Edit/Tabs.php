<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Account_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('account_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('upslabel')->__('UPS account information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('upslabel')->__('Account Information'),
            'title'     => Mage::helper('upslabel')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('upslabel/adminhtml_account_edit_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}