<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Jsinit extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ampgrid/js.phtml');
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    public function getSaveUrl()
    {
        $url = $this->getUrl('ampgrid/adminhtml_field/save');
        if (Mage::getStoreConfig('web/secure/use_in_adminhtml'))
        {
            $url = str_replace(Mage::getStoreConfig('web/unsecure/base_url'), Mage::getStoreConfig('web/secure/base_url'), $url);
        }
        return $url;
    }
    
    public function getSaveAllUrl()
    {
        $url = $this->getUrl('ampgrid/adminhtml_field/saveall');
        if (Mage::getStoreConfig('web/secure/use_in_adminhtml'))
        {
            $url = str_replace(Mage::getStoreConfig('web/unsecure/base_url'), Mage::getStoreConfig('web/secure/base_url'), $url);
        }
        return $url;
    }

    public function getSaveSortingUrl()
    {
        $url = $this->getUrl('ampgrid/adminhtml_field/savesorting');
        if (Mage::getStoreConfig('web/secure/use_in_adminhtml'))
        {
            $url = str_replace(Mage::getStoreConfig('web/unsecure/base_url'), Mage::getStoreConfig('web/secure/base_url'), $url);
        }
        return $url;
    }
    
    public function getColumnsProperties()
    {
        return Mage::helper('ampgrid')->getColumnsProperties();
    }
    
    public function getStoreId()
    {
        $storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        return $storeId;
    }
}