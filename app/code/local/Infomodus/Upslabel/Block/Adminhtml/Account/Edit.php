<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'upslabel';
        $this->_controller = 'adminhtml_account';

        $this->_updateButton('save', 'account', Mage::helper('upslabel')->__('Save Account'));
        $this->_updateButton('delete', 'account', Mage::helper('upslabel')->__('Delete Account'));


        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('account_data') && Mage::registry('account_data')->getId()) {
            return Mage::helper('upslabel')->__("Edit Account '%s'", $this->htmlEscape(Mage::registry('account_data')->getCompanyname()));
        } else {
            return Mage::helper('upslabel')->__('Add account');
        }
    }
}