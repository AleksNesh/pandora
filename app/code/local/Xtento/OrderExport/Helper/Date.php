<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-04-12T00:12:34+02:00
 * File:          app/code/local/Xtento/OrderExport/Helper/Date.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Helper_Date extends Mage_Core_Helper_Abstract
{
    protected $_locale;

    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }

    /*
     * Convert date to UTC
     */
    public function convertDate($date, $useTime = false, $endOfDay = false, $locale = false)
    {
        try {
            if (!$locale) {
                $locale = $this->getLocale()->getLocaleCode();
            }
            $dateObj = $this->getLocale()->date(null, null, $locale, false);

            //set default timezone for store (admin)
            $dateObj->setTimezone(Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));

            if (!$useTime) {
                if ($endOfDay) {
                    //set end of day
                    $dateObj->setHour(23);
                    $dateObj->setMinute(59);
                    $dateObj->setSecond(59);
                } else {
                    //set beginning of day
                    $dateObj->setHour(00);
                    $dateObj->setMinute(00);
                    $dateObj->setSecond(00);
                }
            }

            //set date with applying timezone of store
            if ($useTime) {
                $dateObj->set($date, Varien_Date::DATETIME_INTERNAL_FORMAT, $locale);
            } else {
                $dateObj->set($date, Varien_Date::DATE_INTERNAL_FORMAT, $locale);
            }

            //convert store date to default date in UTC timezone without DST
            $dateObj->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

            return $dateObj;
        } catch (Exception $e) {
            return null;
        }
    }

    /*
     * Convert date to local timezone timestamp. This is important so strftime() in the XSL Template returns the correct time zone.
     */
    public function convertDateToStoreTimestamp($date, $store = null)
    {
        try {
            $dateObj = new Zend_Date();
            $dateObj->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
            $dateObj->set($date, Varien_Date::DATETIME_INTERNAL_FORMAT);
            $dateObj->setLocale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $store));
            $dateObj->setTimezone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $store));
            $gmtOffset = $dateObj->getGmtOffset();
            if ($gmtOffset >= 0) {
                return (int)$dateObj->get(null, Zend_Date::TIMESTAMP) + $gmtOffset;
            } else {
                return (int)$dateObj->get(null, Zend_Date::TIMESTAMP) - $gmtOffset;
            }
        } catch (Exception $e) {
            return null;
        }
    }
}