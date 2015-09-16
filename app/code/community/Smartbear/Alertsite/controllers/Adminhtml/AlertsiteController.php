<?php
class Smartbear_Alertsite_Adminhtml_AlertsiteController extends Mage_Adminhtml_Controller_Action
{

    protected $_publicActions = array('resetalertsite', 'index');

	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu('alertsite/alertsite');

		return $this;
	}

    public function createAction()
    {
        $this->_title($this->__('Create AlertSite Account'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function advanceAction()
    {
        /** @var $api Smartbear_Alertsite_Model_AlertsiteApi */
        $api = Mage::getModel('alertsite/alertsiteapi');
        $api->login();

        $this->_title($this->__('Recover AlertSite Account'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function provisionAction()
    {

        $formValues = new Varien_Object(Mage::app()->getRequest()->getParams());

        /** @var $api Smartbear_Alertsite_Model_AlertsiteApi */
        $api = Mage::getModel('alertsite/alertsiteapi');

        try{
            $apiResponse = $api->provisionAccount($formValues);
        }catch(Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        //If the API response is true aka success, then we'll redirect the user to the config section of Magento
        if($apiResponse)
        {
            $configUrl = $this->getUrl('*/system_config/edit');
            $this->getResponse()->setRedirect($configUrl);
        }
        //Else we want to show the error message and display the site up form again
        else
        {
            $this->getResponse()->setRedirect(Mage::getModel('adminhtml/url')->getUrl('*/alertsite/create'));
        }

    }

    public function advancedEditAction()
    {
        $valid = true;

        $formValues = new Varien_Object(Mage::app()->getRequest()->getParams());

        /** @var $api Smartbear_Alertsite_Model_AlertsiteApi */
        $api = Mage::getModel('alertsite/alertsiteapi');

        //Let's first check to be sure that the data isn't all the same
        if($formValues->getBasicSiteId() == $api->getDeviceId() &&
            $formValues->getDejaUrlId() == $api->getDejaclickDeviceId() &&
            strtolower($formValues->getLoginEmail()) == strtolower($api->getUsername()) &&
            $formValues->getPassword() == $api->getPassword())
        {
            Mage::getSingleton('adminhtml/session')->addSuccess("All settings identical");
            $this->getResponse()->setRedirect(Mage::getModel('adminhtml/url')->getUrl('*/alertsite/advance'));
        }

        //Check to make sure all form fields are set before continueing
        if(!$formValues->getBasicSiteId() || !$formValues->getDejaUrlId() || !$formValues->getLoginEmail() || !$formValues->getPassword())
        {
            Mage::getSingleton('adminhtml/session')->addError('All fields are required to recover/update your account');
            $valid = false;
        }

        //We have all required fields, now let's check them all
        if($valid)
        {
            //First we'll chek to be sure we can login with the username/password provided
            $valid = $api->login($formValues->getLoginEmail(), $formValues->getPassword());

            if(!$valid)
            {
                Mage::getSingleton('adminhtml/session')->addError('Unable to login with provided Login Email and Account Password values - please check');
            }
        }

        //Then we'll check each DejaClick and Device ID to be sure they exist and are enabled
        if($valid)
        {
            $valid = $api->verifyDejaDevice($formValues->getDejaUrlId(), $formValues->getLoginEmail());
            if(!$valid)
            {
                Mage::getSingleton('adminhtml/session')->addError("Problem with DejaClick Device ID - " . $api->getErrorMessage());
            }
        }

        if($valid)
        {
            $valid = $api->verifySiteDevice($formValues->getBasicSiteId(), $formValues->getLoginEmail());
            if(!$valid)
            {
                Mage::getSingleton('adminhtml/session')->addError("Problem with Site Device ID - " . $api->getErrorMessage());
            }
        }

        if(!$valid)
        {
            Mage::getSingleton('core/session')->setFormData($formValues);
            $this->getResponse()->setRedirect(Mage::getModel('adminhtml/url')->getUrl('*/alertsite/advance'));
        }
        else
        {
            //We've validated their account details, now we need to actually save the information to the config
            //$api->getApiReturnData()->
            $api->setCustId((string)$api->getApiReturnData()->Request->Custid);
            $api->setLogin(strtolower($formValues->getLoginEmail()));
            $api->setPass($formValues->getPassword());
            $api->setSiteUrl((string)$api->getApiReturnData()->Request->Device->Descrip);
            $api->setSiteId($formValues->getBasicSiteId());
            $api->setDejaId($formValues->getDejaUrlId());

            $api->save();

            Mage::getSingleton('adminhtml/session')->addSuccess("AlertSite configuration saved");
            $this->getResponse()->setRedirect(Mage::getModel('adminhtml/url')->getUrl('*/alertsite/advance'));
        }

    }

    /**
     * todo: delete this?
     *
     * This is to help us quickly reset all of the modules config values so we can test in various states
     *
     */
    public function resetalertsiteAction()
    {
        $CONFIG_ACTIVATION_URL = 'alertsite/alertsite_config/alertsite_activation_email';
        $CONFIG_DEVICE_ID = 'alertsite/alertsite_config/device_id';
        $CONFIG_CLIENT_ID = 'alertsite/alertsite_config/client_id';
        $CONFIG_ALERTSITE_USER = 'alertsite/alertsite_config/alertsite_user';
        $CONFIG_ALERTSITE_PASS = 'alertsite/alertsite_config/alertsite_pass';
        $CONFIG_ALERTSITE_DEVICE_URL = 'alertsite/alertsite_config/device_url';
        $CONFIG_ALERTSITE_DEVICE_DESCRIPTION = 'alertsite/alertsite_config/device_description';
        $CONFIG_DEJACLICK_DEJACLICK_DEVICE_ID = 'alertsite/alertsite_config/dejaclick_device_id';
        $CONFIG_ALERTSITE_PROVISIONED = 'alertsite/alertsite_config/provisioned';
        $CONFIG_ALERTSITE_PHONE = 'alertsite/alertsite_config/alertsite_phone';
        $CONFIG_ALERTSITE_ENABLED = 'alertsite/alertsite_config/enabled';

        //Setup user info
        Mage::getConfig()->saveConfig($CONFIG_ACTIVATION_URL, "", 'default', 0);
        Mage::getConfig()->saveConfig($CONFIG_ALERTSITE_USER, "", 'default', 0);
        Mage::getConfig()->saveConfig($CONFIG_ALERTSITE_PASS, "", 'default', 0);
        Mage::getConfig()->saveConfig($CONFIG_CLIENT_ID, "", 'default', 0);
        Mage::getConfig()->saveConfig($CONFIG_ALERTSITE_DEVICE_URL, "", 'default', 0);
        Mage::getConfig()->saveConfig($CONFIG_ALERTSITE_DEVICE_DESCRIPTION, "", 'default', 0);
        Mage::getConfig()->saveConfig($CONFIG_DEVICE_ID, "", 'default', 0);
        Mage::getConfig()->saveConfig('alertsite/alertsite_config/enabled', "1", 'default', 0);
        Mage::getConfig()->saveConfig($CONFIG_DEJACLICK_DEJACLICK_DEVICE_ID, "");
        Mage::getConfig()->saveConfig($CONFIG_ALERTSITE_PROVISIONED, 0);
        Mage::getConfig()->saveConfig($CONFIG_ALERTSITE_PHONE, "");
        Mage::getConfig()->saveConfig($CONFIG_ALERTSITE_ENABLED, true);

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        $configUrl = Mage::getUrl('adminhtml/system_config/edit', array('section' => 'alertsite'));
        $this->getResponse()->setRedirect($configUrl);
    }

    public function scatterplotAction()
    {
        if(!Mage::helper('alertsite')->isSetup())
        {
            $message = $this->__('You have not yet created your account.');
            Mage::getSingleton('adminhtml/session')->addError($message);
            $url = Mage::getUrl('adminhtml/system_config/edit', array('section' => 'alertsite'));
            $this->getResponse()->setRedirect($url);
            return;
        }

        $this->_title($this->__('Scatter Plot - AlertSite'));
        $this->loadLayout();
        $this->renderLayout();
    }

    public function benchmarkAction()
    {
        if(!Mage::helper('alertsite')->isSetup())
        {
            $message = $this->__('You have not yet created your account.');
            Mage::getSingleton('adminhtml/session')->addError($message);
            $url = Mage::getUrl('adminhtml/system_config/edit', array('section' => 'alertsite'));
            $this->getResponse()->setRedirect($url);
            return;
        }

        $this->_title($this->__('Benchmarks - AlertSite'));
        $this->loadLayout();
        $this->renderLayout();
    }


}
