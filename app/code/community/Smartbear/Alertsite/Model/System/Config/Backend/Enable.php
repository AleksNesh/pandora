<?php
/**
 * Backend config model for changing our device URL
 */
class Smartbear_Alertsite_Model_System_Config_Backend_Enable extends Mage_Core_Model_Config_Data
{

    // const for this config value
    const XML_PATH_DEVICE_URL = 'alertsite/alertsite_config/enabled';

    /**
     * Get equipped with API!
     *
     * @return Smartbear_Alertsite_Model_AlertsiteApi
     */
    public function getApi()
    {
        return Mage::getModel('alertsite/alertsiteapi');
    }

    /**
     * Saves our url to our SMARTBEAR ALERTSITE account
     *
     * @throws Exception
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        $enable = $this->getValue();

        $api = $this->getApi();
        $result = $api->enableMonitor($enable);

        if (!$result)
        {
            /** @var $session Mage_Admin_Model_Session */
            $session = Mage::getSingleton('admin/session');

            $session->addError('You have disabled the Alertsite extension but there was a problem with disabling Alertsite monitoring - please contact <a href="http://help.alertsite.com/MagentoHelp">support</a>.');
        }

        return parent::save();
    }
}