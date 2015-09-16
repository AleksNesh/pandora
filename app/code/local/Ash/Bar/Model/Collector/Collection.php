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
 * Collection collector model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Collector_Collection extends Ash_Bar_Model_Collector_Abstract
{
    /**
     * Internal collector of processed collections
     *
     * @var array
     */
    protected $_collections = array();

    /**
     * Method called by events to trigger a collector's duties.
     *
     * @param  Varien_Event_Observer $observer
     * @return Ash_Bar_Model_Collector_Abstract
     */
    public function collectData(Varien_Event_Observer $observer)
    {
        parent::collectData($observer);

        // save data
        $this->_collections[] = $observer->getEvent()->getCollection();
    }

    /**
     * The passed object should be populated with data that will be rendered to
     * object for use within the toolbar.
     *
     * @param  stdClass $object
     * @return stdClass
     */
    public function prepareObjectForRender(stdClass $object)
    {
        // create empty object
        $object->collections         = array();
        $object->collectionFileNames = array();

        // if data has been collected, inject in
        foreach ($this->_collections as $_model) {
            $className = get_class($_model);
            if (!array_key_exists($className, $object->collections)) {
                $object->collections[$className] = 0;
            }
            $object->collections[$className]++;
            $object->collectionFileNames[$className] = $this->_getClassFile($className);
        }

        return $object;
    }
}
