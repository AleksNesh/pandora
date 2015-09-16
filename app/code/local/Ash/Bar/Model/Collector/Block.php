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
 * Block collector model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Collector_Block extends Ash_Bar_Model_Collector_Abstract
{
    /**
     * Internal collector of processed blocks
     *
     * @var array
     */
    protected $_blocks = array();

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
        $this->_blocks[] = $observer->getEvent()->getBlock();
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
        $object->layout         = new stdClass();
        $object->blocks         = array();
        $object->blockFileNames = array();

        // if data has been collected, inject in
        foreach ($this->_blocks as $_block) {
            $className = get_class($_block);

            // don't include this extension in list
            if(strpos($className, 'Ash_Bar') === 0) {
                continue;
            }

            // build special key out of class and template name
            $template = $this->_getTemplateNameFromBlock($_block);
            $key      = $className . '::' . $template;

            if (!array_key_exists($key, $object->blocks)) {
                $object->blocks[$key] = 0;
            }
            $object->blocks[$key]++;
            $object->blockFileNames[$key] = $this->_getClassFile($className);
        }

        // add layout data
        $object->layout->handles = $this->_getLayout()->getUpdate()->getHandles();
        // $object->designPaths = Mage::getModel('commercebug/designpathinfo')->getData();

        return $object;
    }

    /**
     * Pull template name out of the block object
     *
     * @param  Mage_Core_Block_Abstract $block
     * @return string
     */
    protected function _getTemplateNameFromBlock($block)
    {
        $file = $block->getTemplateFile();
        $file = !preg_match('{/$}i', $file) ? $file : false;
        $file = $file ? $file : $block->getTemplate();

        return $file;
    }
}
