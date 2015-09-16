<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Pickup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'upslabel';
        $this->_controller = 'adminhtml_pickup';

        $this->_updateButton('save', 'pickup', Mage::helper('upslabel')->__('Save Pickup'));
        $this->_updateButton('delete', 'pickup', Mage::helper('upslabel')->__('Delete Pickup'));


        if (Mage::registry('pickup_data') && Mage::registry('pickup_data')->getId()) {
            $this->_removeButton('save');
            $this->_addButton('saveandcontinue', array(
                'label' => Mage::helper('adminhtml')->__('Modify And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
            ), -100);
            if(Mage::registry('pickup_data')->getStatus() != "Canceled"){
                /*$this->_addButton('status', array(
                    'label' => Mage::helper('upslabel')->__('Get status'),
                    'onclick' => 'statusPickup()',
                    'class' => 'save',
                ), -100);*/
                $this->_addButton('cancel', array(
                    'label' => Mage::helper('upslabel')->__('Cancel pickup'),
                    'onclick' => 'cancelPickup()',
                    'class' => 'save',
                ), -100);
            }
            else {
                $this->_removeButton('saveandcontinue');
            }
        }
        else {
            $this->_addButton('saveandcontinue', array(
                'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
            ), -100);
        }
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
        if (Mage::registry('pickup_data') && Mage::registry('pickup_data')->getId()) {
            $this->_formScripts[] = "
            function cancelPickup(){
                var conf = confirm('Are you sure?');
                if(conf==true){
                    window.location.assign('" . Mage::helper("adminhtml")->getUrl("upslabel/adminhtml_pickup/cancel/id/" . Mage::registry('pickup_data')->getId()) . "');
                }
            }
        ";
        }
        if (Mage::registry('pickup_data') && Mage::registry('pickup_data')->getId()) {
            $this->_formScripts[] = "
            function statusPickup(){
                window.location.assign('" . Mage::helper("adminhtml")->getUrl("upslabel/adminhtml_pickup/status/id/" . Mage::registry('pickup_data')->getId()) . "');
            }
        ";
        }
    }

    public function getHeaderText()
    {
        if (Mage::registry('pickup_data') && Mage::registry('pickup_data')->getId()) {
            $title = new Infomodus_Upslabel_Block_Adminhtml_Pickup_Edit_Render_Title();
            return Mage::helper('upslabel')->__("Edit Pickup '%s'", $this->htmlEscape($title->render(Mage::registry('pickup_data'))));
        } else {
            return Mage::helper('upslabel')->__('Add Pickup');
        }
    }
}