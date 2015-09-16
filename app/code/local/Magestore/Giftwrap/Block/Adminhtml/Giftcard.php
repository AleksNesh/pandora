<?php

class Magestore_Giftwrap_Block_Adminhtml_Giftcard extends Mage_Adminhtml_Block_Template {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('giftwrap/giftcardlist.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('grid', $this->getLayout()->createBlock('giftwrap/adminhtml_giftcard_grid', 'newsletter.template.grid'));
        return parent::_prepareLayout();
    }

    public function getCreateUrl() {
        return $this->getUrl('*/*/new');
    }

    public function getHeaderText() {
        return Mage::helper('giftwrap')->__('Manage Gift Cards');
    }

}