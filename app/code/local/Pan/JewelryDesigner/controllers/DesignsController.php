<?php
/**
 * Pan_JewelryDesigner Extension
 *
 * @category  Pan
 * @package   Pan_JewelryDesigner
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Pan_JewelryDesigner_DesignsController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Pan_JewelryDesigner_Helper_Data
     */
    protected $_helper;

    public function _construct()
    {
        $this->_helper = Mage::helper('pan_jewelrydesigner');
    }

    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    public function indexAction()
    {
        // Get current layout state
        $this->_initAction();

        if ($this->_helper->isEnabled()) {
            // create and render the designer UI html interface
            $block  = $this->getLayout()
                ->createBlock('pan_jewelrydesigner/designer')
                ->setTemplate('application.phtml');
        } else {
            // create and render the disabled designer interface
            $block  = $this->getLayout()
                ->createBlock('pan_jewelrydesigner/template')
                ->setTemplate('disabled.phtml');
        }

        $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
        $this->getLayout()->getBlock('content')->append($block);
        $this->_initLayoutMessages('core/session');
        $this->renderLayout();
    }

}
