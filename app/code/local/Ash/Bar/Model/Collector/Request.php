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
 * Request collector model
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Model_Collector_Request extends Ash_Bar_Model_Collector_Abstract
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

        // save data
        $request = new stdClass();
        $request->controllerAction = $observer->getControllerAction();
        $request->requestObject    = Mage::app()->getRequest();
        $this->setData('request', $request);
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
        $object->controller = new stdClass();
        $object->request    = new stdClass();

        // if data has been collected, inject in
        if ($this->getData('request')->controllerAction
            instanceof Mage_Core_Controller_Varien_Action) {
            $controller = $this->getData('request')->controllerAction;

            $object->controller->className      = get_class($controller);
            $object->controller->fileName       = $this->_getClassFile($controller);
            $object->controller->fullActionName = $controller->getFullActionName();
        }
        if ($this->getData('request')->requestObject
            instanceof Mage_Core_Controller_Request_Http) {
            $request = $this->getData('request')->requestObject;

            $object->request->moduleName     = $request->getModuleName();
            $object->request->controllerName = $request->getControllerName();
            $object->request->actionName     = $request->getActionName();
            $object->request->pathInfo       = $request->getPathInfo();
            $object->request->pageId         = $request->getParam('page_id');
            $object->request->pageId         = ($object->request->pageId)
                ? $object->request->pageId : 'N/A';
        }

        return $object;
    }
}
