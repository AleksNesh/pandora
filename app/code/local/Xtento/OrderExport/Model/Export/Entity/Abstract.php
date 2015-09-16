<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-06-27T16:08:09+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Entity/Abstract.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

abstract class Xtento_OrderExport_Model_Export_Entity_Abstract extends Mage_Core_Model_Abstract
{
    protected $_collection;
    private $_returnArray = array();

    protected function _construct()
    {
        parent::_construct();
    }

    protected function _runExport($forcedCollectionItem = false)
    {
        $exportFields = array();
        if ($forcedCollectionItem === false) {
            #if ($this->getProfile()->getExportFields() !== '') {
            #    $exportFields = explode(",", $this->getProfile()->getExportFields());
            #}
            $collectionCount = null;
            $currItemNo = 1;
            $originalCollection = $this->_collection;
            $currPage = 1;
            $lastPage = 0;
            $break = false;
            while ($break !== true) {
                $collection = clone $originalCollection;
                $collection->setPageSize(100);
                $collection->setCurPage($currPage);
                $collection->load();
                if (is_null($collectionCount)) {
                    $collectionCount = $collection->getSize();
                    $lastPage = $collection->getLastPageNumber();
                }
                if ($currPage == $lastPage) {
                    $break = true;
                }
                $currPage++;
                foreach ($collection as $collectionItem) {
                    if ($this->getExportType() == Xtento_OrderExport_Model_Export::EXPORT_TYPE_TEST || $this->getProfile()->validate($collectionItem)) {
                        $returnData = $this->_exportData(new Xtento_OrderExport_Model_Export_Entity_Collection_Item($collectionItem, $this->_entityType, $currItemNo, $collectionCount), $exportFields);
                        if (!empty($returnData)) {
                            $this->_returnArray[] = $returnData;
                            $currItemNo++;
                        }
                    }
                }
            }
        } else {
            if ($this->getExportType() == Xtento_OrderExport_Model_Export::EXPORT_TYPE_TEST || $this->getProfile()->validate($forcedCollectionItem)) {
                $returnData = $this->_exportData(new Xtento_OrderExport_Model_Export_Entity_Collection_Item($forcedCollectionItem, $this->_entityType, 1, 1), $exportFields);
                if (!empty($returnData)) {
                    $this->_returnArray[] = $returnData;
                }
            }
        }
        #var_dump($this->_returnArray); die();
        return $this->_returnArray;
    }

    public function setCollectionFilters($filters)
    {
        foreach ($filters as $filter) {
            foreach ($filter as $attribute => $filterArray) {
                $this->_collection->addAttributeToFilter($attribute, $filterArray);
            }
        }
        return $this->_collection;
    }

    protected function _exportData($collectionItem, $exportFields)
    {
        return Mage::getSingleton('xtento_orderexport/export_data')
            ->setShowEmptyFields($this->getShowEmptyFields())
            ->setProfile($this->getProfile() ? $this->getProfile() : new Varien_Object())
            ->setExportFields($exportFields)
            ->getExportData($this->_entityType, $collectionItem);
    }

    public function runExport($forcedCollectionItem = false)
    {
        return $this->_runExport($forcedCollectionItem);
    }
}