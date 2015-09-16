<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-03-09T18:00:10+01:00
 * File:          app/code/local/Xtento/OrderExport/Model/Observer/Abstract.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

abstract class Xtento_OrderExport_Model_Observer_Abstract extends Mage_Core_Model_Abstract
{
    /*
     * Add store, date, status, ... filters based on profile settings
     */
    protected function _addProfileFilters($profile)
    {
        $filters = array();
        $profileFilterStoreIds = explode(",", $profile->getStoreIds());
        if (!empty($profileFilterStoreIds)) {
            $storeIds = array();
            foreach ($profileFilterStoreIds as $storeId) {
                if ($storeId != '0' && $storeId != '') {
                    array_push($storeIds, $storeId);
                }
            }
            if (!empty($storeIds)) {
                if ($profile->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
                    $filters[] = array('store_id' => array('in' => $storeIds));
                } else {
                    $filters[] = array('main_table.store_id' => array('in' => $storeIds));
                }
            }
        }
        $profileFilterStatus = explode(",", $profile->getExportFilterStatus());
        if (!empty($profileFilterStatus)) {
            $statuses = array();
            foreach ($profileFilterStatus as $status) {
                if ($status !== '') {
                    array_push($statuses, $status);
                }
            }
            if (!empty($statuses)) {
                if ($profile->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_ORDER) {
                    $filters[] = array('main_table.status' => array('in' => $statuses));
                } else {
                    $filters[] = array('main_table.state' => array('in' => $statuses));
                }
            }
        }
        $dateRangeFilter = array();
        $profileFilterDatefrom = $profile->getExportFilterDatefrom();
        if (!empty($profileFilterDatefrom)) {
            $dateRangeFilter['date'] = true;
            #$dateRangeFilter['from'] = sprintf('%s 00:00:00', $profileFilterDatefrom);
            $dateRangeFilter['from'] = Mage::helper('xtento_orderexport/date')->convertDate($profileFilterDatefrom);
            #$dateRangeFilter['to']->add(1, Zend_Date::HOUR);
        }
        $profileFilterDateto = $profile->getExportFilterDateto();
        if (!empty($profileFilterDateto)) {
            $dateRangeFilter['date'] = true;
            #$dateRangeFilter['to'] = sprintf('%s 23:59:59', $profileFilterDateto);
            #$dateRangeFilter['to'] = Mage::helper('xtento_orderexport/date')->convertDate($profileFilterDateto, false, true);
            $dateRangeFilter['to'] = Mage::helper('xtento_orderexport/date')->convertDate($profileFilterDateto/*, false, true*/);
            $dateRangeFilter['to']->add('1', Zend_Date::DAY);
        }
        $profileFilterCreatedLastXDays = $profile->getData('export_filter_last_x_days');
        if (!empty($profileFilterCreatedLastXDays)) {
            $profileFilterCreatedLastXDays = intval(preg_replace('/[^0-9]/', '', $profileFilterCreatedLastXDays));
            if ($profileFilterCreatedLastXDays > 0) {
                /*$dateToday = Mage::app()->getLocale()->date();
                $dateToday->sub($profileFilterCreatedLastXDays, Zend_Date::DAY);
                $dateRangeFilter['date'] = true;
                $dateRangeFilter['from'] = $dateToday->toString('yyyy-MM-dd 00:00:00');*/
                $dateToday = Zend_Date::now();
                $dateToday->sub($profileFilterCreatedLastXDays, Zend_Date::DAY);
                $dateToday->setHour(00);
                $dateToday->setSecond(00);
                $dateToday->setMinute(00);
                $dateToday->setLocale(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE));
                $dateToday->setTimezone(Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));
                $dateRangeFilter['date'] = true;
                $dateRangeFilter['from'] = $dateToday->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            }
        }
        if (!empty($dateRangeFilter)) {
            if ($profile->getEntity() == Xtento_OrderExport_Model_Export::ENTITY_CUSTOMER) {
                $filters[] = array('created_at' => $dateRangeFilter);
            } else {
                $filters[] = array('main_table.created_at' => $dateRangeFilter);
            }
        }
        return $filters;
    }
}