<?php
/**
 * Backend config model for changing our device URL
 */
class Smartbear_Alertsite_Model_System_Config_Backend_Editdevice extends Mage_Core_Model_Config_Data
{

    // const for this config value
    const XML_PATH_DEVICE_URL = 'alertsite/alertsite_config/device_url';

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
    public function _beforeSave()
    {
        $api = $this->getApi();
        $url = $this->getValue();

        if ($url != $api->getDeviceMonitorUrl())
        { // new url doesn't match old url...
            $result = $api->updateDeviceUrl($url);

            if (!$result)
            {
                throw new Exception($api->getLastApiMessage());
            }

            Mage::getConfig()->saveConfig('alertsite/alertsite_config/device_description', $url);

        }
    }
}