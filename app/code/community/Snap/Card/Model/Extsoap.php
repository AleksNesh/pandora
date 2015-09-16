<?php
/**
 * Created by PhpStorm.
 * User: abystritskiy
 * Date: 19.05.14
 * Time: 22:47
 */

class Snap_Card_Model_Extsoap extends SoapClient
{
    const SOAP_REQUEST_FILE  = 'extended_soap_requests.log';

    /**
     * Do request
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $one_way
     * @return string
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $this->_logRequest($location, $action, $version, $request);
        $response = parent::__doRequest($request, $location, $action, $version, $one_way);
        $this->_logResponse($location, $action, $version, $response);
        return $response;
    }


    /**
     * Get path data
     *
     * @return string
     */
    protected function getPathData()
    {
        $path = Mage::app()->getRequest()->getModuleName() . '/';
        $path .= Mage::app()->getRequest()->getActionName() . '/';
        $path .= Mage::app()->getRequest()->getControllerName();
        return $path;
    }

    /**
     * Log request
     *
     * @param string $location
     * @param string $action
     * @param string $version
     * @param string $request
     */
    protected function _logRequest($location, $action, $version, $request)
    {
        Mage::log("SOAP REQUEST {$this->getPathData()}:", null, self::SOAP_REQUEST_FILE, true);
        Mage::log("Request: " . $request . "\nLocation: " . $location . "\nHeaders: " .
            $this->__getLastRequestHeaders(), null, self::SOAP_REQUEST_FILE, true);
    }

    /**
     * Log response
     *
     * @param string $location
     * @param string $action
     * @param string $version
     * @param string $response
     */
    protected function _logResponse($location, $action, $version, $response)
    {
        Mage::log("SOAP RESPONSE  {$this->getPathData()}:", null, self::SOAP_REQUEST_FILE, true);
        Mage::log("Response: " . $response . "\nLocation: " . $location . "\nHeaders: " .
            $this->__getLastResponseHeaders(), null, self::SOAP_REQUEST_FILE, true);
    }
}