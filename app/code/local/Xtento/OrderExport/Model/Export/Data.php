<?php

/**
 * Product:       Xtento_OrderExport (1.2.5)
 * ID:            GR6WnvnT6Ww3/JikLV7jXYKkCzueYchFmw1tJG+eutg=
 * Packaged:      2013-08-20T14:50:25+00:00
 * Last Modified: 2013-05-17T15:23:30+02:00
 * File:          app/code/local/Xtento/OrderExport/Model/Export/Data.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_OrderExport_Model_Export_Data extends Mage_Core_Model_Abstract
{
    private $registeredExportData = array();

    protected function _construct()
    {
        $this->_getRegisteredExportData();
        parent::_construct();
    }

    private function _getRegisteredExportData()
    {
        $etcDir = Mage::helper('xtcore/filesystem')->getModuleDir($this);
        // Load registered export data
        $exportDataFile = $etcDir . DS . 'xtento' . DS . 'export_data.xml';
        $this->_loadExportDataFile($exportDataFile);
        // Users own export data file
        $exportOwnDataFile = $etcDir . DS . 'xtento' . DS . 'export_data.own.xml';
        $this->_loadExportDataFile($exportOwnDataFile, false);
    }


    private function _loadExportDataFile($exportDataFile, $throwFileException = true)
    {
        if (file_exists($exportDataFile) || is_readable($exportDataFile)) {
            $exportData = simplexml_load_file($exportDataFile);
            if ($exportData) {
                foreach ($exportData->data->children() as $exportName => $exportConfig) {
                    array_push($this->registeredExportData, array('name' => $exportName, 'config' => $exportConfig));
                }
            } else {
                Mage::throwException('Could not load export_data.xml file for data exporting. File broken? Location: ' . $exportDataFile);
            }
        } else {
            if ($throwFileException) {
                Mage::throwException('Could not load export_data.xml file for data exporting. File does not exist or is not readable. Location: ' . $exportDataFile);
            }
        }
    }

    public function getExportData($entityType, $collectionItem = false, $getConfiguration = false)
    {
        #$exportFields = $this->getExportFields();
        $exportData = array();
        foreach ($this->registeredExportData as $data) {
            $className = current($data['config']->class);
            $classIdentifier = str_replace('xtento_orderexport/export_data_', '', $className);
            $exportClass = Mage::getSingleton($className);
            if ($exportClass) {
                #$memBefore = memory_get_usage();
                #echo "Before - ".$data['config']->class.": $memBefore<br>";
                if ($getConfiguration) {
                    if ($exportClass->getEnabled() && $exportClass->confirmDependency() && in_array($entityType, $exportClass->getApplyTo())) {
                        $exportData[] = array('class' => $className, 'class_identifier' => $classIdentifier, 'configuration' => $exportClass->getConfiguration());
                    }
                } else {
                    #echo $classIdentifier, print_r($this->getExportFields(),1)."\n";
                    #if (empty($exportFields) || in_array($classIdentifier, $exportFields)) {
                    if (!in_array($entityType, $exportClass->getApplyTo())) {
                        continue;
                    }
                    if (!$exportClass->getEnabled() || !$exportClass->confirmDependency()) {
                        continue;
                    }
                    $returnData = $exportClass
                        ->setProfile($this->getProfile())
                        ->setShowEmptyFields($this->getShowEmptyFields())
                        ->getExportData($entityType, $collectionItem);
                    if (is_array($returnData)) {
                        $exportData = array_merge_recursive($exportData, $returnData);
                    }
                    #}
                }
                #echo "After: ".memory_get_usage()." (Difference: ".round((memory_get_usage() - $memBefore) / 1024 / 1024, 2)." MB)<br>";
            }
        }
        #Zend_Debug::dump($collectionItem); die();
        $exportData = array_merge_recursive($exportData, $this->_addPrivateFields($collectionItem, $exportData));
        return $exportData;
    }

    private function _addPrivateFields($collectionItem, $exportData)
    {
        $privateFields = array();
        if ($collectionItem !== FALSE && $collectionItem->getObject()) {
            if (!isset($exportData['entity_id'])) {
                $privateFields['entity_id'] = $collectionItem->getObject()->getId();
            }
            if (!isset($exportData['store_id'])) {
                $privateFields['store_id'] = $collectionItem->getObject()->getStoreId();
            }
            if (!isset($exportData['created_at'])) {
                $privateFields['created_at'] = $collectionItem->getObject()->getCreatedAt();
            }
        }
        return $privateFields;
    }
}