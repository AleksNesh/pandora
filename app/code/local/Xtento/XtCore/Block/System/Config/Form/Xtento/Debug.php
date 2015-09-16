<?php

/**
 * Product:       Xtento_XtCore (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2014-07-02T19:53:22+02:00
 * File:          app/code/local/Xtento/XtCore/Block/System/Config/Form/Xtento/Debug.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_XtCore_Block_System_Config_Form_Xtento_Debug extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /*
     * Debug information is shown at System > Configuration > XTENTO Extensions > General Configuration
     */
    protected function _getHeaderHtml($element)
    {
        $headerHtml = parent::_getHeaderHtml($element);
        $debugInfo = array();
        try {
            // Fetch public IP address of server - important if you have failing FTP transfers and need to add the public IP address to the firewall, etc.
            $url = 'https://www.xtento.com/license/info/getip';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $ipAddress = file_get_contents($url);
            } else {
                $client = new Zend_Http_Client($url, array('timeout' => 10));
                $response = $client->request('GET');
                $ipAddress = $response->getBody();
            }
        } catch (Exception $e) {
            return '------------------------------------------------<div style="display:none">Exception: ' . $e->getMessage() . '</div>' . $headerHtml;
        }

        $debugInfo[] = "Public Server IP Address: $ipAddress";
        $debugInfo[] = "PHP memory_limit: " . ini_get('memory_limit');
        $debugInfo[] = "PHP max_execution_time: " . ini_get('max_execution_time');
        $debugInfo[] = "Magento Base Path: " . Mage::getBaseDir();

        $headerHtml = str_replace('<table cellspacing="0" class="form-list">', implode("<br/>", $debugInfo) . '<table cellspacing="0" class="form-list">', $headerHtml);
        return $headerHtml;
    }
}