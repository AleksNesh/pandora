<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Lists_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'upslabel';
        $this->_controller = 'adminhtml_upslabel';

        $this->_updateButton('save', 'upslabel', Mage::helper('upslabel')->__('Save label'));
        $this->_updateButton('delete', 'upslabel', Mage::helper('upslabel')->__('Delete label'));


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
        return Mage::helper('upslabel')->__("Edit label '%s'", $this->htmlEscape(Mage::registry('upslabel_data')->getTitle()));
    }
}