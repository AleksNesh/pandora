<?php

class Snap_Card_Block_SnapTab extends Mage_Core_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    protected $_template = 'snap/snapTab.phtml';
    
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("snap/snapTab.phtml");
    }

    //down here are the mandatory methods you have to include
    public function getTabLabel()
    {
        return $this->__('SNAP Transactions');
    }

    public function getTabTitle()
    {
        return $this->__('SNAP Gift Card Transactions');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
?>
