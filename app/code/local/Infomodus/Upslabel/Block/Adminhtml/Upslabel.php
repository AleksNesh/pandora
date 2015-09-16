<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */
?>
<?php
class Infomodus_Upslabel_Block_Adminhtml_Upslabel extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_upslabel';
        $this->_blockGroup = 'upslabel';
        $this->_headerText = Mage::helper('upslabel')->__('Item Manager');
        /*$this->_addButtonLabel = Mage::helper('upslabel')->__('Add Item');*/
        parent::__construct();
    }
}