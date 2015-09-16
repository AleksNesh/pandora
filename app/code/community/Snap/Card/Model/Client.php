<?php
/**
 * API client model
 *
 * @category    Snap
 * @package     Snap_Card
 * @author      alex
 */

class Snap_Card_Model_Client extends Mage_Core_Model_Abstract
{
    public function __construct()
    {
        $wsdl = 'https://api-test.profitpointinc.com:8417/v4/transaction?wsdl';
        $options = array("connection_timeout" => 20);
        $client = new Snap_Card_Model_Extsoap($wsdl, $options);

        $inquiry = new stdClass();
        $inquiry->intgegrationAuth   = 'm01Auth';
        $inquiry->integrationPassword= 'm01PW';
        $inquiry->requestId          = '1';
        $inquiry->systemId           = 'PP';
        $inquiry->clientId           = 'TEST';
        $inquiry->locationId         = 'MAGENTO01';
        $inquiry->terminalId         = 'WS';
        $inquiry->initiatorType      = 'E';
        $inquiry->initiatorId        = 'wse01';
        $inquiry->initiatorPassword  = '1234';

        $params = array(
            'standardHeader'=>$inquiry
        ); //Request params
        $options = array();

        try {
            $success = $client->__soapCall('Inquiry', array($params), $options);
            Zend_Debug::dump($success); die();
        } catch (Exception $e) {
            Zend_Debug::dump($e->__toString());
            die();
        }
        }



    protected function _getAuthenticationHeader()
    {

        $attributes = new stdClass();
        $attributes->intgegrationAuth   = 'm01Auth';
        $attributes->integrationPassword= 'm01PW';
        $attributes->requestId          = '1';
        $attributes->systemId           = 'PP';
        $attributes->clientId           = 'TEST';
        $attributes->locationId         = 'MAGENTO01';
        $attributes->terminalId         = 'WS';
        $attributes->initiatorType      = 'E';
        $attributes->initiatorId        = 'wse01';
        $attributes->initiatorPassword  = '1234';

        $soapHdr = new SoapHeader('urn:SparkbaseTransactionWsdl', 'standardHeader', $attributes);
        return $soapHdr;
    }
}