<?php

/**
 * Product:       Xtento_GridActions (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2014-07-26T17:55:23+02:00
 * File:          app/code/local/Xtento/GridActions/Model/System/Config/Backend/Import/Server.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Model_System_Config_Backend_Import_Server extends Mage_Core_Model_Config_Data
{
    const VERSION = 'o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=';

    public function afterLoad()
    {
        $extId = 'Xtento_Spob152689';
        $sPath = 'gridactions/general/';
        $sName1 = $this->getFirstName();
        $sName2 = $this->getSecondName();
        $this->setValue(base64_encode(base64_encode(base64_encode($extId . ';' . trim(Mage::getModel('core/config_data')->load($sPath . 'serial', 'path')->getValue()) . ';' . $sName2 . ';' . Mage::getUrl() . ';' . Mage::getSingleton('admin/session')->getUser()->getEmail() . ';' . Mage::getSingleton('admin/session')->getUser()->getName() . ';' . @$_SERVER['SERVER_ADDR'] . ';' . $sName1 . ';' . self::VERSION . ';' . Mage::getModel('core/config_data')->load($sPath . 'enabled', 'path')->getValue() . ';' . (string)Mage::getConfig()->getNode()->modules->Xtento_GridActions->version))));
    }

    public function getFirstName()
    {
        $table = Mage::getModel('core/config_data')->getResource()->getMainTable();
        $readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $readConn->select()->from($table, array('value'))->where('path = ?', 'web/unsecure/base_url')->where('scope_id = ?', 0)->where('scope = ?', 'default');
        $url = str_replace(array('http://', 'https://', 'www.'), '', $readConn->fetchOne($select));
        $url = explode('/', $url);
        $url = array_shift($url);
        $parsedUrl = parse_url($url, PHP_URL_HOST);
        if ($parsedUrl !== null) {
            return $parsedUrl;
        }
        return $url;
    }

    public function getSecondName()
    {
        $url = str_replace(array('http://', 'https://', 'www.'), '', @$_SERVER['SERVER_NAME']);
        $url = explode('/', $url);
        $url = array_shift($url);
        $parsedUrl = parse_url($url, PHP_URL_HOST);
        if ($parsedUrl !== null) {
            return $parsedUrl;
        }
        return $url;
    }
}
