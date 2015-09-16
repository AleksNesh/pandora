<?php
/**
 * Backend config model for changing our account info
 */
class Smartbear_Alertsite_Model_System_Config_Backend_Editaccount extends Mage_Core_Model_Config_Data
{

    // const for this config value
    const XML_PATH_ACCOUNT_USERNAME = 'alertsite/alertsite_config/alertsite_user';
    const XML_PATH_ACCOUNT_PHONE    = 'alertsite/alertsite_config/alertsite_phone';

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
     * Saves our account information to the internet before we save everything
     *
     * @throws Exception
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $api = $this->getApi();

        $update = false;
        $login = $this->getData('groups/alertsite_config/fields/alertsite_user/value');
        $phone = $this->getData('groups/alertsite_config/fields/alertsite_phone/value');
        $firstName = $this->getData('groups/alertsite_config/fields/alertsite_first_name/value');
        $lastName = $this->getData('groups/alertsite_config/fields/alertsite_last_name/value');

        $request = new Varien_Object();


        if ($login != $api->getUsername())
        {
            $request->setLogin($login);
            $update = true;
        }

        if ($phone != $api->getPhone())
        {
            $request->setContactPhone($phone);
            $update = true;
        }

        if ($firstName != $api->getFirstName())
        {
            $request->setFirstName($firstName);
            $update = true;
        }

        if ($lastName != $api->getLastName())
        {
            $request->setLastName($lastName);
            $update = true;
        }


        if ($update)
        {
            $updatedResponse = $api->updateAccount($request);

            if (!$updatedResponse)
            {
                throw new Exception($api->getLastApiMessage());
            }

        }

    }
}