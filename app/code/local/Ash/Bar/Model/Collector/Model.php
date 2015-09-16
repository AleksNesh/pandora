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
 * Model collector model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Collector_Model extends Ash_Bar_Model_Collector_Abstract
{
    /**
     * Internal collector of processed models
     *
     * @var array
     */
    protected $_models = array();

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
        $this->_models[] = $observer->getEvent()->getObject();
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
        $object->models = array();
        $object->modelFileNames = array();

        // if data has been collected, inject in
        foreach ($this->_models as $_model) {
            $className = get_class($_model);
            if (!array_key_exists($className, $object->models)) {
                $object->models[$className] = 0;
            }
            $object->models[$className]++;
            $object->modelFileNames[$className] = $this->_getClassFile($className);
        }

        return $object;
    }
}
