<?php
/**
* Ash Slideshow Extension
*
* @category  Ash
* @package   Ash_Slideshow
* @copyright Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
* @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* @author    August Ash Team <core@augustash.com>
*
**/

class Ash_Slideshow_Adminhtml_SlideshowsController extends Mage_Adminhtml_Controller_Action
{
    protected $_helper;
    protected $_slideshowModel;
    protected $_slideshowAssetsModel;

    /**
     * preDispatch - called before every action
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_helper                  = Mage::helper('ash_slideshow');
        $this->_slideshowModel          = Mage::getModel('ash_slideshow/slideshow');
        $this->_slideshowAssetsModel    = Mage::getModel('ash_slideshow/slideshowasset');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ash_slideshow/slideshows')
            ->_addBreadcrumb($this->_helper->__('Slideshow Manager'), $this->_helper->__('Slideshow Manager'));
        return $this;
    }

    /**
     * _initSlideshow - find or create a slideshow object
     * @return Ash_Slideshow_Model_Slideshowslides
     */
    protected function _initSlideshow()
    {
        $slideshowId    = (int) $this->getRequest()->getParam('id');
        $slideshow      = $this->_slideshowModel;
        if ($slideshowId) {
            $slideshow->load($slideshowId);
        }
        Mage::register('current_slideshow', $slideshow);
        return $slideshow;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $slideshowId    = $this->getRequest()->getParam('id');
        $slideshow      = $this->_initSlideshow();

        if ($slideshowId && !$slideshow->getId()) {
            $this->_getSession()->addError($this->_helper->__('Slideshow does not exist'));
            $this->_redirect('*/*/');
            return;
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $slideshow->setData($data);
        }

        Mage::register('slideshow_slide_data', $slideshow);

        $this->loadLayout();
        $this->_setActiveMenu('ash_slideshow/tiles');

        // breadcrumbs
        $this->_addBreadcrumb($this->_helper->__('Slideshow Manager'), $this->_helper->__('Slideshow Manager'));
        $this->_addBreadcrumb($this->_helper->__('Slideshow Edit'), $this->_helper->__('New Slideshow'));

        $this->_addContent($this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow_edit'));
        $this->_addLeft($this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow_edit_tabs'));

        // Enable ExtJS
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('ash_slideshow/tiles');
        $this->_addBreadcrumb($this->_helper->__('Slide Manager'), $this->_helper->__('Slideshow Manager'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow_new'));
        $this->_addLeft($this->getLayout()->createBlock('ash_slideshow/adminhtml_slideshow_new_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$this->getRequest()->getPost()) {
            $this->_getSession()->addError('Failed to save slide. Please check log.');
            Mage::log("Failed to save slide. The request was not a post as expected.");
            $this->_redirect('*/*/');
        }

        if ($postData = $this->getRequest()->getPost()) {
            try {
                $slideshowData  = $postData['slideshow'];
                $slideshow      = $this->_initSlideshow();
                $slideshowId    = $slideshowData['id'];
                $assets         = (array_key_exists('assets', $postData)) ? $postData['assets'] : array();
                $justNow        = date('Y-m-d H:i:s', time());

                // update the slideshow
                $slideshow->addData($slideshowData);
                $slideshow->save();

                if (empty($slideshowId)) {
                    $slideshowId = $slideshow->getId();
                }

                // map slideshow assets to this slideshow
                $this->updateAssetReferences($slideshowId, $assets);

                $this->_getSession()->addSuccess($this->__('Slide was successfully saved'));
                $this->_getSession()->setSlideshowData(false);
                $this->_redirect('*/*/');
            } catch(Exception $e) {
                Mage::log("FROM CLASS " . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
                Mage::log($e->getMessage());

                $this->_getSession()->addError("Unable to save slide because of this error: " . $e->getMessage());
                $this->_getSession()->setSlideshowData($this->getRequest()->getPost());

                $this->_redirectToEdit();
            }
        }
    }

    public function deleteAction()
    {
        try {
            $slideshow = $this->_initSlideshow();
            $slideshow->delete();

            $this->_getSession()->addSuccess($this->_helper->__('Slideshow was successfully deleted'));
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            Mage::log("FROM CLASS " . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
            Mage::log($e->getMessage());

            $this->_getSession()->addError($e->getMessage());
            $this->_redirectToEdit();
        }
    }

    private function updateAssetReferences($slideId, $assets)
    {
        // get pivot relationship table model
        $pivotModel = $this->_slideshowAssetsModel->getCollection();

        // Delete all asset values for this relationship
        $pivotModel->delete($slideId);

        // Add the new values for this relationship
        $pivotModel->addAssets($slideId, $assets);
    }


    public function slideassetsortAction()
    {
        $slideshowAssetModel    = $this->_slideshowAssetsModel;
        $postData               = $this->getRequest()->getPost();

        if(isset($postData) && !empty($postData)) {
            $slideshowAssetModel->update_asset_order($postData);
        }
    }


    protected function _redirectToEdit()
    {
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        return;
    }

}
