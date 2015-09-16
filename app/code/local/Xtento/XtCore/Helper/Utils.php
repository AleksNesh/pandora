<?php

/**
 * Product:       Xtento_XtCore (1.7.7)
 * ID:            o5J5Fxf1uEhWScFFa24PUq6DVEzgtn6EKR9tAUroEmE=
 * Packaged:      2014-08-04T20:41:36+00:00
 * Last Modified: 2014-07-05T13:22:21+02:00
 * File:          app/code/local/Xtento/XtCore/Helper/Utils.php
 * Copyright:     Copyright (c) 2014 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_XtCore_Helper_Utils extends Mage_Core_Helper_Abstract
{
    protected $_modules = false;

    protected $_versionCorrelationEE_CE = array(
        '1.9.1.0' => '1.4.2.0',
        '1.9.1.1' => '1.4.2.0',
        '1.10.0.1' => '1.5.0.1',
        '1.10.1.0' => '1.5.1.0',
        '1.10.1.1' => '1.5.1.0',
        '1.11.0.0' => '1.6.0.0',
        '1.11.0.2' => '1.6.0.0',
        '1.11.1.0' => '1.6.1.0',
        '1.11.2.0' => '1.6.1.0',
        '1.12.0.0' => '1.7.0.0',
        '1.12.0.1' => '1.7.0.0',
        '1.12.0.2' => '1.7.0.0',
        '1.13.0.0' => '1.8.0.0',
        '1.13.0.2' => '1.8.0.0',
        '1.13.1.0' => '1.8.1.0',
        '1.14.0.0' => '1.9.0.0',
        '1.14.0.1' => '1.9.0.1'
    );

    protected $_versionCorrelationPE_CE = array(
        '1.9.1.0' => '1.4.2.0',
        '1.9.1.1' => '1.4.2.0',
        '1.10.0.1' => '1.5.0.1',
        '1.10.1.0' => '1.5.1.0',
        '1.11.0.0' => '1.6.0.0',
        '1.11.1.0' => '1.6.1.0',
    );

    /* Thanks for the inspiration to Sortal. */
    public function mageVersionCompare($version1, $version2, $operator)
    {
        // Detect edition by included modules
        if (!$this->_modules) {
            $this->_modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        }

        $version1 = preg_replace("/[^0-9\.]/", "", $version1);

        if (in_array('Enterprise_CatalogPermissions', $this->_modules)) {
            // Detected enterprise edition
            if (!isset($this->_versionCorrelationEE_CE[$version1])) {
                return version_compare($version1, $version2, $operator);
            } else {
                return version_compare($this->_versionCorrelationEE_CE[$version1], $version2, $operator);
            }
        } elseif (in_array('Enterprise_Enterprise', $this->_modules)) {
            // Detected professional edition
            if (!isset($this->_versionCorrelationPE_CE[$version1])) {
                return version_compare($version1, $version2, $operator);
            } else {
                return version_compare($this->_versionCorrelationPE_CE[$version1], $version2, $operator);
            }
        } else {
            // Detected community edition
            return version_compare($version1, $version2, $operator);
        }
    }

    // Check if a third party extension is installed and enabled
    public function isExtensionInstalled($extensionIdentifier)
    {
        if (!$this->_modules) {
            $this->_modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        }
        if (in_array($extensionIdentifier, $this->_modules)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Is the module running in a Magento Professional or Enterprise Edition installation?
     */
    public function getIsPEorEE()
    {
        // Detect edition by included modules
        if (!$this->_modules) {
            $this->_modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        }

        if (in_array('Enterprise_CatalogPermissions', $this->_modules)) {
            // Detected enterprise edition
            return true;
        } elseif (in_array('Enterprise_Enterprise', $this->_modules)) {
            // Detected professional edition
            return true;
        } else {
            // Detected community edition
            return false;
        }
    }

    public function isCronRunning()
    {
        return Mage::getModel('xtcore/observer_cron')->checkCronjob();
    }

    public function getLastCronExecution()
    {
        return Mage::getModel('xtcore/observer_cron')->getLastExecution();
    }

    /**
     * @param $newMemoryLimit
     *
     * Increase memory limit to $newMemoryLimit, but only if current value is lower
     */
    public function increaseMemoryLimit($newMemoryLimit)
    {
        $currentLimit = ini_get('memory_limit');
        if ($currentLimit == -1) {
            // No limit, no need to increase
            return true;
        }
        $currentLimitInBytes = $this->_convertToByte($currentLimit);
        $newMemoryLimitInBytes = $this->_convertToByte($newMemoryLimit);
        if ($currentLimitInBytes < $newMemoryLimitInBytes) {
            @ini_set('memory_limit', $newMemoryLimit);
            return true;
        } else {
            return false;
        }
    }

    protected function _convertToByte($value)
    {
        if (stripos($value, 'G') !== false) {
            return (int)$value * pow(1024, 3);
        } elseif (stripos($value, 'M') !== false) {
            return (int)$value * 1024 * 1024;
        } elseif (stripos($value, 'K') !== false) {
            return (int)$value * 1024;
        }
        return (int)$value;
    }

    /**
     * @return null|Zend_Mail_Transport_Smtp
     *
     * Support for custom email transports
     */
    public function getEmailTransport()
    {
        $transport = null;
        if (Mage::helper('xtcore/utils')->isExtensionInstalled('Aschroder_SMTPPro') && Mage::helper('smtppro')->isEnabled()) {
            // SMTPPro extension
            $transport = Mage::helper('smtppro')->getTransport();
        } else if (Mage::helper('xtcore/utils')->isExtensionInstalled('AW_Customsmtp') && Mage::getStoreConfig('customsmtp/general/mode') != AW_Customsmtp_Model_Source_Mode::OFF) {
            // AW_Customsmtp extension
            $config = array(
                'port' => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_PORT), //optional - default 25
                'auth' => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_AUTH),
                'username' => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_LOGIN),
                'password' => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_PASSWORD)
            );

            $needSSL = Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_SSL);
            if (!empty($needSSL)) {
                $config['ssl'] = Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_SSL);
            }

            $transport = new Zend_Mail_Transport_Smtp(Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_HOST), $config);
        }
        return $transport;
    }
}