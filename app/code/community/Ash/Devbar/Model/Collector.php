<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Collector model
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Devbar_Model_Collector
{
    /**
     * Array of registered collectors
     *
     * @var array
     */
    protected $_collectors = array();

    /**
     * Register individual collectors. This collection will be used to render
     * JSON to the page. Called from the concreate collector.
     *
     * @param   Ash_Devbar_Model_Collector_Abstract $collector
     * @return  Ash_Devbar_Model_Collector
     */
    public function registerCollector(Ash_Devbar_Model_Collector_Abstract $collector)
    {
        if (!in_array($collector, $this->_collectors)) {
            $this->_collectors[] = $collector;
        }

        return $this;
    }

    /**
     * Gather all the registered collectors' data into a single object and encode
     * to JSON for embedding into the page.
     *
     * @return  string
     */
    public function toJson()
    {
        // populate object with data
        $renderObject = new stdClass();
        foreach ($this->_collectors as $_collector) {
            $renderObject = $_collector->prepareObjectForRender($renderObject);
        }

        // add objects that will be supressed
        $renderObject->suppressedModels = preg_split('%\s%',
            Mage::getStoreConfig('ash_devbar/options/suppressed_classes'));

        // @TODO log collected data

        return Zend_Json::encode($renderObject);
    }
}
