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
 * Collector interface
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
interface Ash_Bar_Model_Collector_Interface
{
    /**
     * Method called by events to trigger a collector's duties.
     *
     * @param  Varien_Event_Observer $observer
     * @return Ash_Bar_Model_Collector_Abstract
     */
    public function collectData(Varien_Event_Observer $observer);

    /**
     * The passed object should be populated with data that will be rendered to
     * JSON for use within the toolbar.
     *
     * @param  stdClass $object
     * @return stdClass
     */
    public function prepareObjectForRender(stdClass $object);

    /**
     * Access control check. Should return boolean value.
     *
     * @param  mixed $object
     * @return boolean
     */
    public function isAllowed($object);
}
