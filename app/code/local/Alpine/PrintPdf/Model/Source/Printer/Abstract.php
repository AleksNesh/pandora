<?php
/**
 * Source model for getting current value of configuration
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Model_Source_Printer_Abstract
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
abstract class Alpine_PrintPdf_Model_Source_Printer_Abstract
{
    protected $_configPath = '';

    public function getAllOptions()
    {
        return array(
            'key'   => Mage::getStoreConfig($this->_configPath),
            'value' => Mage::getStoreConfig($this->_configPath)
        );
    }

    public function toOptionArray()
    {
        return array(
            Mage::getStoreConfig($this->_configPath) => Mage::getStoreConfig($this->_configPath)
        );
    }

}