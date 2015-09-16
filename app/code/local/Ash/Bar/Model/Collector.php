<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Bar
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Collector model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Collector
{
    /**
     * Array of registered collectors
     *
     * @var array
     */
    protected $_collection = array();

    /**
     * Register individual collectors. This collection will be used to render
     * JSON to the page.
     *
     * @param  Ash_Bar_Model_Collector_Abstract $collector
     * @return Ash_Bar_Model_Collector
     */
    public function registerCollector(Ash_Bar_Model_Collector_Abstract $collector)
    {
        // store collector
        if (!in_array($collector, $this->_collection)) {
            $this->_collection[] = $collector;
        }

        return $this;
    }

    /**
     * Gather all the registered collector's data into a single object and encode
     * to JSON for embedding into the page.
     *
     * @return string
     */
    public function toJson()
    {
        // populate object with data
        $renderObject = new stdClass();
        foreach ($this->_collection as $_collector) {
            $renderObject = $_collector->prepareObjectForRender($renderObject);
        }

        // encode as JSON
        $json = Zend_Json::encode($renderObject);

        // @TODO log collected data

        return $json;
    }
}
