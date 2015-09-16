<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php
class Infomodus_Upslabel_Block_Adminhtml_Conformity extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        
        $this->_controller = 'adminhtml_conformity';
        $this->_blockGroup = 'upslabel';
        $this->_headerText = Mage::helper('upslabel')->__('Compliance of Methods');
        $this->_addButtonLabel = Mage::helper('upslabel')->__('Add Conformity');

        $data = array(
            'label' =>  Mage::helper('upslabel')->__('Add Conformity'),
            'class' => 'scalable add',
            'onclick'   => "setLocation('".$this->getUrl('upslabel/adminhtml_conformity/new')."')"
        );
        $this->addButton('conformity_add', $data, 0, 100,  'header', 'header');
        parent::__construct();
        $this->_removeButton('add');
    }
}