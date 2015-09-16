<?php

class Smartbear_Alertsite_Block_Adminhtml_Dashboard extends Mage_Core_Block_Template
{

    var $_template = "alertsite/dashboard.phtml";

    /** @var $_alertsiteApi Smartbear_Alertsite_Model_AlertsiteApi */
    private $_alertsiteApi = null;

    public function __construct()
    {
        parent::__construct();

        if(! Mage::helper('alertsite')->getConfig('alertsite_config', 'enabled') )
        {
            return;
        }

        $this->_alertsiteApi = Mage::getModel('alertsite/alertsiteapi');
        $this->_alertsiteApi->getDeviceStatus();
    }

    public function isEnabled()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config', 'enabled');
    }

    public function getFriendlyStatus()
    {
        return $this->_alertsiteApi->getFriendlyStatus();
    }

    public function getStatusCode()
    {
        return $this->_alertsiteApi->getStatusCode();
    }

    public function getStatusTime()
    {
        $timeAtLastChange = $this->_alertsiteApi->getStatusLastChanged();
        $currentTime = time();
        $timeDiff = $currentTime - $timeAtLastChange;

        /** @var $dateModel Mage_Core_Model_Date */
        $dateModel = Mage::getModel('core/date');

        if ($timeAtLastChange)
            return $dateModel->date('M j, g:i:s A', $timeAtLastChange);
        else
            return 'N/A';
    }

    public function getStatusDescription()
    {
        return $this->_alertsiteApi->getDeviceDescription();
    }

    public function _toHtml()
    {
        if(! Mage::helper('alertsite')->getConfig('alertsite_config', 'enabled') )
        {
            return "</fieldset></div>";
        }

        $html = $this->renderView();
        return $html;

    }

}