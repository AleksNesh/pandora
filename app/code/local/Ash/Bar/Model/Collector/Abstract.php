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
 * Collector abstract model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
abstract class Ash_Bar_Model_Collector_Abstract extends Varien_Object
    implements Ash_Bar_Model_Collector_Interface
{
    /**
     * Method called by events to trigger a collector's duties.
     *
     * @param  Varien_Event_Observer $observer
     * @return Ash_Bar_Model_Collector_Abstract
     */
    public function collectData(Varien_Event_Observer $observer)
    {
        if (!$this->isAllowed($observer)) {
            return;
        }

        // register collector
        Mage::getSingleton('ash_bar/collector')->registerCollector($this);
    }

    /**
     * The passed object should be populated with data that will be rendered to
     * JSON for use within the toolbar.
     *
     * @param  stdClass $object
     * @return stdClass
     */
    abstract public function prepareObjectForRender(stdClass $object);

    /**
     * Inspect a class and get name
     *
     * @param  mixed $className
     * @return string
     */
    protected function _getClassFile($className)
    {
        $r = new ReflectionClass($className);

        return str_replace("'", '', $r->getFileName());
    }

    /**
     * Convenience method for quick access to layout object.
     *
     * @return Mage_Core_Model_Layout
     */
    protected function _getLayout()
    {
        return Mage::getSingleton('core/layout');
    }

    /**
     * Access control check. Currently not in use.
     *
     * @param  mixed $object
     * @return boolean
     */
    public function isAllowed($object)
    {
        return true;
    }
}
