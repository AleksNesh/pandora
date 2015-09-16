<?php

class Fooman_Testing_Helper_Debug extends Mage_Core_Helper_Abstract {
    /**
     * send to Firebug
     *
     * @param $content
     */
    public function sendToFirebug($content) {
        $writer = new Zend_Log_Writer_Firebug();
        $logger = new Zend_Log($writer);

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();
        $channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $channel->setRequest($request);
        $channel->setResponse($response);

        // Start output buffering
        ob_start();

        // Now you can make calls to the logger

        $logger->log($content, Zend_Log::INFO);

        // Flush log data to browser
        $channel->flush();
        $response->sendHeaders();
    }
}