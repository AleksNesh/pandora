<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php

class Infomodus_Upslabel_Block_Adminhtml_Pickup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        
        $this->_controller = 'adminhtml_pickup';
        $this->_blockGroup = 'upslabel';
        $this->_headerText = Mage::helper('upslabel')->__('Pickup Manager');
        $this->_addButtonLabel = Mage::helper('upslabel')->__('Add Pickup');

        $data = array(
            'label' =>  Mage::helper('upslabel')->__('Add Pickup'),
            'class' => 'scalable add',
            'onclick'   => "setLocation('".$this->getUrl('upslabel/adminhtml_pickup/new')."')"
        );
        $this->addButton('pickup_add', $data, 0, 100,  'header', 'header');

        parent::__construct();
        $this->_removeButton('add');
    }
}