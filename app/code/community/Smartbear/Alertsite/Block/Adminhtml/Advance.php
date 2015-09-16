<?php
/**
 * Smartbear_Alertsite_Block_Adminhtml_Notifications
 * Notifications block. Displays a note at the top of adminhtml pages.
 *
 * @category
 * @package     Smartbear_Alertsite
 */
class Smartbear_Alertsite_Block_Adminhtml_Advance extends Mage_Adminhtml_Block_Template
{
    /**
     * Sets the form action urls for the page.
     */
    public function _construct()
    {
        parent::_construct();

        $this->setFormAction(Mage::helper('adminhtml')->getUrl('*/alertsite/advancedEdit'));

        //if(!$formValues->getBasicSiteId() || !$formValues->getDejaUrlId() || !$formValues->getLoginEmail() || !$formValues->getPassword())
        if(Mage::getSingleton('core/session')->getFormData())
        {
            $this->addData(Mage::getSingleton('core/session')->getFormData()->getData());
        }
        $this->formData = Mage::getSingleton('core/session')->unsFormData();
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

    public function getUsername()
    {
        return $this->getLoginEmail() ? $this->getLoginEmail() : $this->getApi()->getUsername();
    }

    public function getSiteDeviceId()
    {
        return $this->getBasicSiteId() ? $this->getBasicSiteId() : $this->getApi()->getDeviceId();
    }

    public function getDejaclickDeviceId()
    {
        return $this->getDejaUrlId() ? $this->getDejaUrlId() : $this->getApi()->getDejaclickDeviceId();
    }

    public function getPassword()
    {
        return $this->getApi()->getPassword();
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
