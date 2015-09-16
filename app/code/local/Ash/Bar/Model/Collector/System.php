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
 * System collector model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Collector_System extends Ash_Bar_Model_Collector_Abstract
{
    /**
     * Method called by events to trigger a collector's duties.
     *
     * @param  Varien_Event_Observer $observer
     * @return Ash_Bar_Model_Collector_Abstract
     */
    public function collectData(Varien_Event_Observer $observer)
    {
        parent::collectData($observer);

        // set AJAX uri
        $system = new stdClass();
        $system->ajaxUri = $this->getUrl();
        $this->setData('system', $system);
    }

    /**
     * Returns base URL for any AJAX request
     *
     * @return string
     */
    public function getUrl()
    {
        $url = Mage::getUrl('ashbar/ajax', array(
            '_secure' => Mage::getModel('core/store')->isCurrentlySecure()
        ));

        return $url;
    }

    /**
     * The passed object should be populated with data that will be rendered to
     * JSON for use within the toolbar.
     *
     * @param  stdClass $object
     * @return stdClass
     */
    public function prepareObjectForRender(stdClass $object)
    {
        // create empty object
        $object->system = new stdClass();

        // if data has been collected, inject in
        if ($this->getData('system') instanceof stdClass) {
            $object->system = $this->getData('system');
        }

        return $object;
    }
}
