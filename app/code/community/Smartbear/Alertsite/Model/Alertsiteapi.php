<?php
/**
 * Alertsite API Model
 *
 *
 * @method string getStatusCode()
 * @method string getStatusLastChanged()
 * @method string getDeviceDescription()
 * @method string getLastResponseTime()
 * @method setStatusCode(string $code)
 * @method setStatusLastChanged(string $date)
 * @method setDeviceDescription(string $description)
 * @method setLastResponseTime(string $time)
 * @method setLastApiStatus(int $status)
 * @method int getLastApiStatus()
 * @method setLastApiMessage(string $message)
 * @method string getLastApiMessage()
 * @method int getSessionId()
 *
 */
class Smartbear_Alertsite_Model_Alertsiteapi extends Mage_Core_Model_Abstract
{

    //Endpoint URLs

	const ALERTSITE_REST_API_URL      = 'https://www.alertsite.com/cgi-bin/alertsite-restapi/index.cgi/'; //'http://www.infield.alertsite.com/cgi-bin/alertsite-restapi/index.cgi/';
	const ALERTSITE_REPORT_API_URL    = 'https://www.alertsite.com/report-api/'; //'http://www.infield.alertsite.com/report-api/';
	const ALERTSITE_PROVISION_API_URL = 'https://www.alertsite.com/cgi-bin/orderform'; // 'http://www.infield.alertsite.com/cgi-bin/orderform';

	//Config paths that are needed for configuration of AlertSite API
    const CONFIG_ACTIVATION_URL = 'alertsite/alertsite_config/alertsite_activation_email';
    const CONFIG_DEVICE_ID = 'alertsite/alertsite_config/device_id';
    const CONFIG_DEJACLICK_DEVICE_ID = 'alertsite/alertsite_config/dejaclick_device_id';
    const CONFIG_CLIENT_ID = 'alertsite/alertsite_config/client_id';
    const CONFIG_ALERTSITE_USER = 'alertsite/alertsite_config/alertsite_user';
    const CONFIG_ALERTSITE_PASS = 'alertsite/alertsite_config/alertsite_pass';
    const CONFIG_ALERTSITE_DEVICE_URL = 'alertsite/alertsite_config/device_url';
    const CONFIG_ALERTSITE_DEVICE_DESCRIPTION = 'alertsite/alertsite_config/device_description';
    const CONFIG_ALERTSITE_ENABLED = 'alertsite/alertsite_config/enabled';
    const CONFIG_ALERTSITE_PROVISIONED = 'alertsite/alertsite_config/provisioned';
    const CONFIG_ALERTSITE_PHONE = 'alertsite/alertsite_config/alertsite_phone';
    const CONFIG_ALERTSITE_FIRST_NAME = 'alertsite/alertsite_config/alertsite_first_name';
    const CONFIG_ALERTSITE_LAST_NAME = 'alertsite/alertsite_config/alertsite_last_name';
    const CONFIG_ALERTSITE_COMPANY = 'alertsite/alertsite_config/alertsite_company';

    protected $_statusApiResult = null;
    protected $_detailApiResult = null;
    protected $_statuses        =
        array(
            "0" => "Site responded normally to all tests",
            "1" => "TCP connection failed",
            "2" => "Test timed out",
            "3" => "Invalid response from server",
            "5" => "Validation failed",
            "6" => "No response from server",
            "7" => "HTTP error from web server",
            "8" => "Web site is redirected (warning)",
            "9" => "Ping failed (site is not responding)",
            "51" => "Unable to resolve IP address",
            "60" => "soapUI-specific error",
            "61" => "soapUI startup error",
            "99" => "Unable to ping from location",
            "80" => "Browser event timeout encountered",
            "82" => "Page took too long to load",
            "83" => "Firefox event notification did not occur",
            "84" => "Page location did not change when expected",
            "85" => "Expected page updates did not occur",
            "86" => "Network activity did not stop",
            "89" => "Internal Browser timeout",
            "81" => "Maximum transaction time was exceeded",
            "90" => "Unexpected processing exception occurred",
            "91" => "Minimum match score not met",
            "92" => "Maximum number of skipped events exceeded",
            "93" => "Missing instruction for dialog prompt",
            "94" => "Maximum transaction steps exceeded",
            "95" => "Unable to monitor from chosen station",
            "96" => "Unable to parse transaction XML",
            "97" => "Target page element was not found",
            "70" => "Remote verification did not detect an error. No alert is generated",
            "71" => "Remote verification has confirmed an error. Alert is generated",
            "72" => "Remote verification response was not received. Alert is generated",
            "73" => "Remote verification response was invalid. Alert is generated",
            "79" => "Remote verification was unavailable. Alert is generated",
            "98" => "Notification (alert) was generated",
            "9095" => "Unable to run test from chosen monitoring location",
            "50XX" => "Mobile Device error where XX corresponds to the codes above",
            "5300" => "Service Execution Error",
            "60XX" => "soapUI error where XX corresponds to the codes above",
            "70XX" => "Page object error where XX corresponds to the codes above",
            "7121" => "Object length changed",
            "7122" => "Missing object",
            "7123" => "New object found",
            "7130" => "No fullpage objects found",
            "7202" => "Fullpage Timeout",
            "4030" => "Warning threshold exceeded for monitored item",
            "4040" => "Error threshold exceeded for monitored item",
            "4050" => "Reported results were not received when expected",
            "4059" => "Ping failed (server not reporting)"
        );


    /**
     * Get the configured username returned from provisioning
     *
     * @return string
     */
    public function getUsername()
    {
        return strtolower(Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_user'));
    }

    /**
     * Get the configured first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_first_name');
    }

    /**
     * Get the configured last name
     *
     * @return string
     */
    public function getLastName()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_last_name');
    }

    /**
     * Get the configured password returned from provisioning
     *
     * @return string
     */
    public function getPassword()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_pass');
    }

    /**
     * Get the configured phone number!
     *
     * @return string
     */
    public function getPhone()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_phone');
    }

    /**
     * Get the configured device id returned from provisioning
     *
     * @return string
     */
    public function getDeviceId()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config','device_id');
    }

    /**
     * Get the configured dejaclick device id returned from provisioning
     *
     * @return string
     */
    public function getDejaclickDeviceId()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config','dejaclick_device_id');
    }

    /**
     * Get the configured customer id returned from provisioning
     *
     * @return string
     */
    public function getCustomerId()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config','client_id');
    }

    /**
     * Get the configured device monitor url
     * @return string
     */
    public function getDeviceMonitorUrl()
    {
        return Mage::helper('alertsite')->getConfig('alertsite_config','device_url');
    }

    /**
     * Get a url for use in a curl call
     *
     * @param string $action
     * @param array $params
     * @param string $api
     * @return string
     */
    public function getUrl($action = '', $params = array(), $api = 'rest')
    {

        $baseUrl = '';

        switch(strtolower($api))
        {
            case 'report':
                $baseUrl = self::ALERTSITE_REPORT_API_URL;
                break;
            case 'rest':
                $baseUrl = self::ALERTSITE_REST_API_URL;
                break;
            case 'provision':
                $baseUrl = self::ALERTSITE_PROVISION_API_URL;
                break;
            default:
                $baseUrl = self::ALERTSITE_REST_API_URL;
        }

        $url = $baseUrl.$action;

        if (!empty($params))
        {
            $customerId = ''; // customer id appears at the beginning of the urls in report calls - might be unused now
            if (array_key_exists('customer_id', $params))
            {
                $customerId = $params['customer_id'];

                unset($params['customer_id']);
            }

            $url .= '/'.$customerId.'?';

            $i = 0;
            $size = count($params);

            foreach ($params as $k => $v)
            {
                $url .= urlencode($k).'='.urlencode($v);
                if ($i!=$size-1)
                    $url .= '&';

                $i++;
            }

        }

        return $url;
    }

    /**
     * Build an XML request for our (Rest) API
     *
     *
     * Here's a typical request:
     *  <Upload>
     *      <APIVersion>1.1</APIVersion>
     *      <Authenticate>
     *          <Login>$LOGIN</Login>
     *          <SessionID>$SESSION_ID</SessionID>
     *      </Authenticate>
     *      <Request>
     *          <ObjDevice>$OBJ_DEVICE</ObjDevice>
     *          <URL>$URL</URL>
     *      </Request>
     *  </Upload>
     *
     * Here's the special case Login request:
     *
     *  <Login>
     *      <ControlID></ControlID>
     *      <KeepAlive>1</KeepAlive>
     *      <Login>foo@bar.com</Login>
     *      <Password>$PASS</Password>
     *  </Login>
     *
     *
     * @param string $root
     * @param bool $autoLogin - automatically log in if we don't have a session id
     * @return SimpleXMLElement
     */
    public function getRequest($root = '', $autoLogin = true)
    {

        $requestXml = new SimpleXMLElement('<'.$root.'/>');


        if ($root == 'Login') // special case login xml
        {

            //todo: we don't really need this except for testing locally
            $ip = $this->getRequestIp();
            if($ip == "127.0.0.1")
                $ip = '76.14.75.242';

            if($ip)
                $requestXml->SessionIP  = $ip;
//            else
//                $requestXml->SessionIP  = $_SERVER['SERVER_ADDR'];

            $requestXml->KeepAlive  = '1';
            $requestXml->Login      = $this->getUsername();
            $requestXml->Password   = $this->getPassword();


        } else
        {
            $requestXml->APIVersion = '1.1';

            // set up login and session
            $requestXml->Authenticate->Login = $this->getUsername();

            if ($autoLogin && !$this->getSessionId())
                $this->login(); // if we don't have a session id, we have to log in... this will trigger the special case code above.

            $requestXml->Authenticate->SessionID = $this->getSessionId();
        }

        return $requestXml;
    }

    /**
     * Curl function to make a post or get request and get the resulting response.
     *
     * Called with the URL to use, the request object if necessary, what kind of request you want and whether or not
     * you are making an XML request!
     *
     * @param string $url
     * @param SimpleXMLElement|null $request todo maybe make this a string someday
     * @param string $action
     * @param bool $xml
     * @return SimpleXMLElement|bool
     */
    public function getCurlResponse($url, $request = null, $action = 'POST', $xml = true)
    {
        if ($action == 'POST')
        {
            // curl set up
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);

            if($xml)
            {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request->asXML());
//                Mage::Log('Request - '.$request->asXML(), null, 'alertsite.log', true);

            }
            else
            {
                $fieldString = '';

                foreach($request as $key=>$value) { $fieldString .= $key.'='.$value.'&'; }

                $fieldString = rtrim($fieldString, '&');

                curl_setopt($ch,CURLOPT_POST, count($request));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);

            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);

//            Mage::Log('Response - '.$output->asXML(), null, 'alertsite.log', true);
            curl_close($ch);

            // todo everything went swell check
            $output = $this->getObjectFromXml($output);

            //We will set the return data on the API object so it's accessible later if we need raw access
            $this->setApiReturnData($output);

            return $output;
        }

        if ($action == 'GET')
        {
            // todo graceful failure on username not defined
            $username = $this->getUsername();
            $password = $this->getPassword();

            //Get the result from the AlertSite/SmartBear API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $output = curl_exec($ch);
            curl_close($ch);

            $output = $this->getObjectFromXml($output);

            return $output;
        }

        return false;
    }


    /**
     * Process an XML response string to create an XML object
     *
     * @param $xmlString
     * @return bool|SimpleXMLElement
     */
    public function getObjectFromXml($xmlString)
    {
        try {
            $output = simplexml_load_string($xmlString);
        }
        catch(Exception $ex) {
            //todo: check if we should log this message
            return false;
        }

        return $output;
    }

    /**
     * Login to the SMARTBEAR ALERTSITE Api
     *
     * @return bool
     */
    public function login($login = false, $pass = false)
    {
        $request = $this->getRequest('Login');

        //If a user or pass is passed in then we'll use those instaed
        if($login !== false && $pass !== false)
        {
            $request->Login = strtolower($login);
            $request->Password = $pass;
        }

        // curl set up
        $url = $this->getUrl('user/login');

        $response = $this->getCurlResponse($url, $request);

        if ($response && property_exists($response, 'Status') && $response->Status == '0' && property_exists($response, 'SessionID'))
        {
            $this->setSessionId((string) $response->SessionID);
            return true;
        }
        else // there was an error,
        {
            //Check to see if the response code was 47, i.e. account was not activated yet
            if($response && property_exists($response, 'Status') && $response->Status == '47')
            {
                $activationUrl = Mage::helper('alertsite')->getConfig('alertsite_config', 'alertsite_activation_email');
                Mage::getSingleton('adminhtml/session')->addError('Your AlertSite account has not yet been activated. Click <a target="_blank" onclick="setTimeout(\'window.location=window.location;\', 2000);" href="'.$activationUrl.'">here</a> to enable your account.');
                return false;
            }
        }

    }



    /**
     * Path: devices/download
     *
     * Request Body XML:
     * <Download>
     * 	<Authenticate>
     * 		<Login>bob</Login>
     * 		<SessionID>ef770427109e343e</SessionID>
     * 	</Authenticate>
     * 	<Request>
     * 		<ControlID>control_555</ControlID>
     * 		<ObjDevice>83621</ObjDevice>
     * 	</Request>
     * </Download>
     *
     * @return SimpleXMLElement
     */
    public function getDevice()
    {
        $request = $this->getRequest('Download');
        $request->Request->ObjDevice = $this->getDeviceId();

        $url = $this->getUrl('devices/download');

        $response = $this->getCurlResponse($url, $request);

        return $response;
    }

    protected function verifyDevice($deviceId, $login, $deviceTypes)
    {
        $valid = true;

        $request = $this->getRequest('Download');
        $request->Request->ObjDevice = $deviceId;

        $request->Authenticate->Login = strtolower($login);

        $url = $this->getUrl('devices/download');

        $response = $this->getCurlResponse($url, $request);

        $device =$response->Request->Device;
        $deviceType = (string)$device->DeviceType;
        $monitorEnabled = (string)$device->Monitor;

        if((string)$response->Request->Status != '0')
        {
            $valid = false;
            $this->setErrorMessage('Device ID '.$deviceId.' seems to be invalid');
        }
        else if(!in_array($deviceType, $deviceTypes))
        {
            //Check to make sure the type matches the type it should be
            $valid = false;
            $this->setErrorMessage('Device type mismatch - please be sure device ID '.$deviceId.' entered matches the type listed');
        }
        else if($monitorEnabled != 'y')
        {
            //todo: are we sure this isn't valid?
            $valid = false;
            $this->setErrorMessage('Device ID ' . $deviceId . ' does not currently have monitoring enabled');
        }

        return $valid;

    }

    public function verifyDejaDevice($deviceId, $login)
    {
        return $this->verifyDevice($deviceId, $login, array('m'));;
    }

    public function verifySiteDevice($deviceId, $login)
    {
        return $this->verifyDevice($deviceId, $login, array('w', 's'));
    }


    /**
     * Get the device status for this account!
     *
     *    Path: devices/status
     *
     *    Request Body XML:
     *    <Status>
     *       <APIVersion>1.1</APIVersion>
     *       <Authenticate>
     *                    <Login>foo@bar.com</Login>
     *                    <SessionID>17d63297eb5956be</SessionID>
     *       </Authenticate>
     *        <Request>
     *                    <ControlID>5555-99</ControlID>
     *                    <ObjDevice>159560</ObjDevice>
     *        </Request>
     *    </Status>
     *
     * @return SimpleXMLElement|bool
     */
    public function getDeviceStatus()
    {

        // set up our request!
        $request = $this->getRequest('Status');
        $request->Request->ObjDevice = $this->getDeviceId(); // todo no device id
        $request->Request->ControlID = 1;
        $url = $this->getUrl('devices/status');

        // prepare response!
        /** @var $response SimpleXMLElement */
        $response = $this->getCurlResponse($url, $request);

        if ($response && property_exists($response, 'Request'))
        {
            // if we got a valid response back, we should check for errors
            /** @var $requestResponse SimpleXMLElement */
            $requestResponse = $response->Request;

            // if there aren't any errors...
            if ($requestResponse
                && property_exists($requestResponse, 'Status')
                && $requestResponse->Status == '0')
            {

                // grab that device
                $device = $requestResponse->Device; // todo NO DEVICE?

                // do what we used to do
                $this->setStatusCode((string)$device->StatusCode);
                $this->setStatusLastChanged((string)$device->DtLastStatus);
                $this->setDeviceDescription((string)$device->Descrip);
            }

            return $response;
        }

        return false;
    }

    /**
     *  Update our account information.
     *
     *    Path: account/update
     *
     *    Request body XML:
     *    <Update>
     *      <Authenticate>
     *         <Login>bob@foo.com</Login>
     *         <SessionID>10d0f31bbcc57750</SessionID>
     *      </Authenticate>
     *      <Request>
     *         <ControlID>control 12345</ControlID>
     *         <User>
     *             <SelectedLogin>bob@foo.com</SelectedLogin>
     *             <Login>joe@foo.com</Login>
     *             <ContactPhone1>954-312-1111</ContactPhone1>
     *             <ContactPhone2>954-312-2222</ContactPhone2>
     *         </User>
     *         <Company>My New Company Name</Company>
     *         <TimeZone>-5</TimeZone>
     *         <DST>US</DST>
     *      </Request>
     *    </Update>
     *
     *
     * @param Varien_Object $info
     * @return bool
     */
    public function updateAccount($info)
    {
        // set up request object
        $request = $this->getRequest('Update');
        $request->Request->ControlID = 1;

        if ($info->getLogin())
        {
            $request->Request->User->SelectedLogin   = $this->getUsername();
            $request->Request->User->Login           = strtolower($info->getLogin());
        }

        if ($info->getContactPhone())
        {
            $request->Request->User->SelectedLogin   = strtolower($this->getUsername());
            $request->Request->User->ContactPhone1   = $info->getContactPhone();
//            $request->Request->User->ContactPhone2   = '';
        }

        if ($info->getFirstName())
        {
            $request->Request->User->SelectedLogin   = strtolower($this->getUsername());
            $request->Request->User->FirstName   = $info->getFirstName();
        }

        if ($info->getLastName())
        {
            $request->Request->User->SelectedLogin   = strtolower($this->getUsername());
            $request->Request->User->LastName   = $info->getLastName();
        }

//        $request->Request->Company = '';
//        $request->Request->TimeZone = '';
//        $request->Request->DST      = '';

        $url = $this->getUrl('account/update');

        // prepare response!
        /** @var $response SimpleXMLElement */
        $response = $this->getCurlResponse($url, $request);

        if ($response && property_exists($response, 'Request'))
        {
            // if we got a valid response back, we should check for errors
            /** @var $requestResponse SimpleXMLElement */
            $requestResponse = $response->Request;

            // if there aren't any errors...
            if ($requestResponse
                && property_exists($requestResponse, 'Status'))
            {

                if ($requestResponse->Status == '0')
                {
                    return true; // success!
                }
                else // we ran into some trouble...
                {
                    $this->setLastApiStatus($requestResponse->Status);

                    if (property_exists($requestResponse, 'Message'))
                        $this->setLastApiMessage($requestResponse->Message.' ('.$this->getLastApiStatus().')');
                }
            }
        }

        // something terrible happened
        return false;
    }

    /**
     *  Update the Device URL for our account!
     *
     *    Path: devices/upload
     *
     *    Request body XML:
     *    <Upload>
     *        <APIVersion>1.1</APIVersion>
     *        <Authenticate>
     *            <Login>foo@bar.com</Login>
     *            <SessionID>17d63297eb5956be</SessionID>
     *        </Authenticate>
     *        <Request>
     *            <ControlID>5555-99</ControlID>
     *            <ObjDevice>159560</ObjDevice>
     *            <HttpFollowRedirect>n</HttpFollowRedirect>
     *            <URL>www.cnn.com</URL>
     *        </Request>
     *        <Request> todo make sure to update the deja click device too...
     *            <ControlID>5555-100</ControlID>
     *            <ObjDevice>159561</ObjDevice>
     *            <HttpFollowRedirect>n</HttpFollowRedirect>
     *            <URL>www.cnn.com</URL>
     *            <Monitor>n</Monitor>
     *        </Request>
     *    </Upload>
     *
     * $LOGIN, $SESSION_ID, $OBJ_DEVICE and $URL need to be replaced with appropriate values.
     * Please note that the "APIVersion" node (with value 1.1) is required to enable the URL node to take effect.
     * Other applicable nodes can be defined in the "Request" node when the "URL" node is defined.
     *
     * @param string $deviceId
     * @param string $url
     * @return bool
     */
    public function updateDeviceUrl($url = '', $deviceId = '', $dejaclickDeviceId = '')
    {
        if (empty($deviceId))
            $deviceId = $this->getDeviceId();

        if (empty($dejaclickDeviceId))
            $dejaclickDeviceId = $this->getDejaclickDeviceId();



        // set up request object
        $request = $this->getRequest('Upload');

        $siteDeviceRequest = $request->addChild('Request');
        $siteDeviceRequest->ObjDevice = $deviceId;
        $siteDeviceRequest->URL = $url;
        $siteDeviceRequest->Descrip = $url;
        $siteDeviceRequest->HttpFollowRedirect = 'n'; // should fix error code 64 - and remove request for function we don't qualify for
        $siteDeviceRequest->ControlID = 1;

        $dejaclickDeviceRequest = $request->addChild('Request');
        $dejaclickDeviceRequest->ObjDevice = $dejaclickDeviceId;
        $dejaclickDeviceRequest->URL = $url;
        $dejaclickDeviceRequest->Descrip = $url;
        $dejaclickDeviceRequest->HttpFollowRedirect = 'n'; // should fix error code 64 - and remove request for function we don't qualify for
        $dejaclickDeviceRequest->ControlID = 1;


        $url = $this->getUrl('devices/upload');

        // prepare response!
        /** @var $response SimpleXMLElement */
        $response = $this->getCurlResponse($url, $request);

        if ($response && property_exists($response, 'Request'))
        {
            // if we got a valid response back, we should check for errors
            /** @var $requestResponse SimpleXMLElement */
            $requestResponse = $response->Request;

            // if there aren't any errors...
            if ($requestResponse
                && property_exists($requestResponse, 'Status'))
            {

                if ($requestResponse->Status == '0')
                {
                    return true; // success!
                }
                else // prepare error response
                {
                    $this->setLastApiStatus($requestResponse->Status);

                    if (property_exists($requestResponse, 'Message'))
                        $this->setLastApiMessage($requestResponse->Message.' ('.$this->getLastStatus().')');
                }
            }
        }

        // something terrible happened
        return false;
    }

    /**
     * Simple toggle function for turning monitor on and off on default device.
     *
     * for request structure
     * @see Smartbear_Alertsite_Model_Alertsiteapi::updateDeviceUrl()
     *
     * @param bool $enable
     * @return bool|SimpleXMLElement
     */
    public function enableMonitor($enable = true)
    {
        if ($enable)
            $enable = 'y';
        else
            $enable = 'n';

        // set up request object
        $request = $this->getRequest('Upload');

        $siteDevice = $request->addChild('Request');
        $siteDevice->ObjDevice = $this->getDeviceId();
        $siteDevice->HttpFollowRedirect = 'n';
        $siteDevice->Monitor = $enable;

        $dejaclickDevice = $request->addChild('Request');
        $dejaclickDevice->ObjDevice = $this->getDejaclickDeviceId();
        $dejaclickDevice->HttpFollowRedirect = 'n';
        $dejaclickDevice->Monitor = $enable;

        $url = $this->getUrl('devices/upload');

        /** @var $response SimpleXMLElement */
        $response = $this->getCurlResponse($url, $request);

        if ($response && property_exists($response, 'Request'))
        {
            // if we got a valid response back, we should check for errors
            /** @var $requestResponse SimpleXMLElement */
            $requestResponse = $response->Request;

            // if there aren't any errors...
            if ($requestResponse
                && property_exists($requestResponse, 'Status'))
            {

                if ($requestResponse->Status == '0')
                {
                    return true; // success!
                }
                else // prepare error response
                {
                    $this->setLastApiStatus($requestResponse->Status);

                    if (property_exists($requestResponse, 'Message'))
                        $this->setLastApiMessage($requestResponse->Message.' ('.$this->getLastStatus().')');
                }
            }
        }

        return false;
    }

    /**
     * This does a lookup in the status array defined at the top of the class to produce a friendly status message.
     *
     * @param bool $code
     * @return bool|string
     */
    public function getFriendlyStatus($code = false)
    {
        if($code === false && $this->hasData('status_code'))
            $code = $this->getStatusCode();
        else
            return false;

        $statuses = $this->_statuses;
        $status = $statuses[$code];
        return $status;
    }

    /**
     * Provision a new account with SmartBear's AlertSite API and return any response
     *
     * @param $parameters
     * @throws Exception - error message encountered when provisioning
     * @return bool - was the account successfully provisioned?
     */
    public function provisionAccount(Varien_Object $parameters)
    {
        //get the provisioning URL
        $url = self::ALERTSITE_PROVISION_API_URL;

        $apiFriendlyParams = array();

        $apiFriendlyParams['fname'] = urlencode($parameters->getFirstName());
        $apiFriendlyParams['lname'] = urlencode($parameters->getLastName());
        $apiFriendlyParams['phone'] = urlencode($parameters->getPhone());
        $apiFriendlyParams['company'] = urlencode($parameters->getCompanyName());
        $apiFriendlyParams['api_version'] = urlencode('1.0');
        $apiFriendlyParams['order_type'] = urlencode('magento');
        $apiFriendlyParams['response_type'] = urlencode('xml');
        $apiFriendlyParams['login_email'] = urlencode(strtolower($parameters->getLoginEmail()));
        $apiFriendlyParams['monitor_url'] = urlencode($parameters->getMonitorUrl());

        $this->setPhone($parameters->getPhone());
        $this->setCompany($apiFriendlyParams['company']);
        $this->setFName($apiFriendlyParams['fname']);
        $this->setLName($apiFriendlyParams['lname']);

        $response = $this->getCurlResponse($url, $apiFriendlyParams, 'POST', false);

        if(!$response)
        {
            throw new Exception("There was a problem provisioning your new account. Please try again or contact AlertSite support.");
            return false;
        }

        $status = (string)$response["status"];

        if($status == 'failed')
        {
            $error = $response->Error;

            $errorMessage = (string)$error['description'];

            if((string)$error['code'] == "0001")
            {

                if($error->missing_fields)
                {
                    $count = count($error->missing_fields);
                    $missingFields = "";

                    if($count > 1)
                    {
                        foreach($error->missing_fields as $missingField)
                        {
                            if((string)$missingField['name'] == 'monitor_url')
                                $missingFields .= " - Monitor URL Required";
                            else if((string)$missingField['name'] == 'login_email')
                                $missingFields .= " - Login Email Required";
                        }
                    }

                    else
                    {
                        if((string)$error->missing_fields['name'] == 'monitor_url')
                            $missingFields .= " - Monitor URL Required";
                        else if((string)$error->missing_fields['name'] == 'login_email')
                            $missingFields .= " - Login Email Required";
                    }

                    $errorMessage .= $missingFields;

                }
            }

            throw new Exception($errorMessage);
        }
        else if($status == 'success')
        {
            $this->_newAlertSiteConfig($response->AccountCreated);
            return true;

        }

        return false;

    }

    /**
     * Helper function to create a new account given a response from the provisioning API
     *
     * todo could use some cleanup.
     *
     * @param SimpleXMLElement $newAccount
     */
    protected function _newAlertSiteConfig($newAccount)
    {
        $account = $newAccount->Account;
        $devices = $newAccount->Devices;
        //Setup user info
        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_ENABLED, true);
        Mage::getConfig()->saveConfig(self::CONFIG_ACTIVATION_URL, (string)$account['activation_url']);
        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_USER, (string)$account['login']);
        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_PASS, (string)$account['password']);
        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_COMPANY, (string)$this->getCompany());
        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_FIRST_NAME, (string)$this->getFName());
        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_LAST_NAME, (string)$this->getLName());


        Mage::getConfig()->saveConfig(self::CONFIG_CLIENT_ID, (string)$account['custid']);
        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_PROVISIONED, 1);

        if($this->getPhone())
            Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_PHONE, $this->getPhone());

        Mage::getSingleton('adminhtml/session')->addSuccess('Thank you for creating your AlertSite account. Click <a target="_blank"  onclick="setTimeout(\'window.location=window.location;\', 2000);" href="'.(string)$account['activation_url'].'">here</a> to enable your account.');

        //Setup Devices
        foreach($devices->Device as $device)
        {
            $deviceType = (string)$device['type'];
            if($deviceType == 'ipd')
            {
                Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_DEVICE_URL, (string)$device['URL']);
                Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_DEVICE_DESCRIPTION, (string)$device['description']);
                Mage::getConfig()->saveConfig(self::CONFIG_DEVICE_ID, (string)$device['obj_device']);
                break;
            }
            else if($deviceType == 'dejaclick')
            {
                Mage::getConfig()->saveConfig(self::CONFIG_DEJACLICK_DEVICE_ID, (string)$device['obj_device']);
            }
        }

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }


    /**
     * This is used when doing an "advance" save
     *
     * @return Mage_Core_Model_Abstract|void
     */
    public function save()
    {

        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_ENABLED, true);

        if($this->getLogin())
            Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_USER, $this->getLogin());

        if($this->getPass())
            Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_PASS, $this->getPass());

        if($this->getCustId())
        Mage::getConfig()->saveConfig(self::CONFIG_CLIENT_ID, $this->getCustId());

        if($this->getCompany())
            Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_COMPANY, $this->getCompany());


        Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_PROVISIONED, 1);

        if($this->getSiteUrl())
            Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_DEVICE_URL, $this->getSiteUrl());

        if($this->getSiteUrl())
            Mage::getConfig()->saveConfig(self::CONFIG_ALERTSITE_DEVICE_DESCRIPTION, $this->getSiteUrl());

        if($this->getSiteId())
            Mage::getConfig()->saveConfig(self::CONFIG_DEVICE_ID, $this->getSiteId());

        if($this->getDejaId())
            Mage::getConfig()->saveConfig(self::CONFIG_DEJACLICK_DEVICE_ID, $this->getDejaId());

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

        return true;

    }

}