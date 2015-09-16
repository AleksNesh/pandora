<?php
/**
 * Smartbear_Alertsite_Block_Adminhtml_Notifications
 * Notifications block. Displays a note at the top of adminhtml pages.
 *
 * @category
 * @package     Smartbear_Alertsite
 */
class Smartbear_Alertsite_Block_Adminhtml_Create extends Mage_Adminhtml_Block_Template
{
    /**
     * Sets the form action urls for the page.
     */
    public function _construct()
    {
        parent::_construct();

        $this->setFormAction(Mage::helper('adminhtml')->getUrl('*/alertsite/provision'));
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }

    public function getFirstname()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if($user && $user->getFirstname())
            return ucwords($user->getFirstname());
        else
            return '';
    }

    public function getLastname()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if($user && $user->getLastname())
            return ucwords($user->getLastname());
        else
            return '';
    }

    public function getCompanyName()
    {
        $storeName = Mage::getStoreConfig('general/store_information/name', Mage::app()->getStore()->getCode());
        if($storeName)
            return $storeName;
        else
            return '';
    }

    public function getStoreUrl()
    {
        $storeUrl = Mage::getStoreConfig('web/secure/base_url', Mage::app()->getStore()->getCode());
        if($storeUrl)
            return $storeUrl;
        else
            return '';
    }

    public function getEmail()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if($user && $user->getEmail())
            return ucwords($user->getEmail());
        else
            return '';
    }

    /**
     * Get the API for retrieving values
     *
     * @return Smartbear_Alertsite_Model_Alertsiteapi
     */
    public function getApi()
    {
        return Mage::getSingleton('alertsite/alertsiteapi');
    }

}
