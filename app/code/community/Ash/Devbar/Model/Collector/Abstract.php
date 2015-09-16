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
 * Collector abstract model
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
abstract class Ash_Devbar_Model_Collector_Abstract extends Varien_Object
    implements Ash_Devbar_Model_Collector_Interface
{
    /**
     * Constructor
     *
     * Registered the object in the collection of collectors
     *
     * @return  void
     */
    public function _construct()
    {
        if ($this->isAllowed()) {
            Mage::getSingleton('ash_devbar/collector')->registerCollector($this);
        } else {
            Mage::log('Access denied! ' . get_class($this) . ' was not registered as a collector.');
        }
    }

    /**
     * Access control check.
     *
     * @TODO Currently not in use.
     *
     * @return  boolean
     */
    public function isAllowed()
    {
        return true;
    }

    /**
     * Inspect a class and get its name
     *
     * @param   mixed $className
     * @return  string
     */
    protected function _getClassFile($className)
    {
        $r = new ReflectionClass($className);

        return str_replace("'", '', $r->getFileName());
    }

    /**
     * Convenience method for quick access to layout object.
     *
     * @return  Mage_Core_Model_Layout
     */
    protected function _getLayout()
    {
        return Mage::getSingleton('core/layout');
    }
}
