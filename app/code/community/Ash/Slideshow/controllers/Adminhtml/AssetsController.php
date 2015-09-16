<?php
/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 *
 */

class Ash_Slideshow_Adminhtml_AssetsController extends Mage_Adminhtml_Controller_Action
{
    protected $_helper;
    protected $_slideshowModel;
    protected $_assetModel;

    protected $_imageUrl;
    protected $_mediaAbsolutePath;
    protected $_prependRandom;

    /**
     * preDispatch - called before every action
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_helper                  = Mage::helper('ash_slideshow');
        $this->_slideshowModel          = Mage::getModel('ash_slideshow/slideshow');
        $this->_assetModel              = Mage::getModel('ash_slideshow/asset');

        $this->_imageUrl                = 'ash_slideshow_assets' . DS . 'image' . DS;
        $this->_mediaAbsolutePath       = Mage::getBaseDir('media');
        $this->_prependRandom           = str_shuffle('abcdefghijklmnopqrstuvxwyz');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('ash_slideshow/tiles')
            ->_addBreadcrumb($this->_helper->__('Assets Manager'), $this->_helper->__('Assets Manager'));
        return $this;
    }

    /**
     * _initAsset - find or create an asset object
     * @return Ash_Slideshow_Model_Slideshowassets
     */
    protected function _initAsset()
    {
        $assetId    = $this->getRequest()->getParam('id');
        $asset      = $this->_assetModel;

        if ($assetId) {
            $asset->load($assetId);
        }

        Mage::register('current_asset', $asset);
        return $asset;
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ash_slideshow/adminhtml_asset'));
        $this->renderLayout();
    }

    public function editAction()
    {
        $assetId    = $this->getRequest()->getParam('id');
        $asset      = $this->_initAsset();

        if ($assetId && !$asset->getId()) {
            $this->_getSession()->addError($this->_helper->__('Asset does not exist'));
            $this->_redirect('*/*/');
            return;
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $asset->setData($data);
        }

        Mage::register('slideshow_asset_data', $asset);

        $this->loadLayout();
        $this->_setActiveMenu('ash_slideshow/tiles');

        // breadcrumbs
        $this->_addBreadcrumb($this->_helper->__('Asset Manager'), $this->_helper->__('Asset Manager'));
        $this->_addBreadcrumb($this->_helper->__('Asset Edit'), $this->_helper->__('New Asset'));

        $this->_addContent($this->getLayout()->createBlock('ash_slideshow/adminhtml_asset_edit'));
        $this->_addLeft($this->getLayout()->createBlock('ash_slideshow/adminhtml_asset_edit_tabs'));

        // Enable ExtJS
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('ash_slideshow/tiles');
        $this->_addBreadcrumb($this->_helper->__('Asset Manager'), $this->_helper->__('Asset Manager'));
        $this->_addBreadcrumb($this->_helper->__('Asset Edit'), $this->_helper->__('New Asset'));
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('ash_slideshow/adminhtml_asset_new'));
        $this->_addLeft($this->getLayout()->createBlock('ash_slideshow/adminhtml_asset_new_tabs'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$this->getRequest()->getPost()) {
            $this->_getSession()->addError('Failed to save Slideshow Asset. Please check log.');
            Mage::log("Failed to save Slideshow Asset. The request was not a post as expected.");
            $this->_redirect('*/*/');
        }

        if ($postData = $this->getRequest()->getPost()) {
            try {
                $asset      = $this->_initAsset();
                $assetId    = $postData['id'];
                $justNow    = date('Y-m-d H:i:s', time());

                $newAssetImageOptons = array(
                    'media_absolute_path' => $this->_mediaAbsolutePath,
                    'file_info'           => $_FILES,
                    'random_string'       => $this->_prependRandom,
                    'media_url'           => $this->_imageUrl,
                    'field_slug'          => 'asset_image',
                );

                // update the asset
                $asset->addData($postData);

                // upload the image
                if (array_key_exists('asset_image', $_FILES) && !empty($_FILES['asset_image']['name'])) {
                    $uploader = new Varien_File_Uploader('asset_image');

                    // append the file name to the relative path set in the preDispatch method
                    $imageName = Mage::helper('ash_slideshow')->assetImageUpload($uploader, $newAssetImageOptons);

                    $this->_imageUrl .= $imageName;

                    // Delete previous image if any on update
                    if($asset->getId()) {
                        $assetImage = $this->_helper->deleteImage($asset['image']);
                    }
                    $asset->setData('image', $this->_imageUrl);
                }

                // save the asset
                $asset->save();

                $this->_getSession()->addSuccess($this->__('Asset was successfully saved'));
                $this->_getSession()->setSlideshowAssetData(false);
                $this->_redirect('*/*/');
            } catch(Exception $e) {
                Mage::log("FROM CLASS " . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
                Mage::log($e->getMessage());

                $this->_getSession()->addError("Unable to save asset because of this error: " . $e->getMessage());
                $this->_getSession()->setSlideshowAssetData($this->getRequest()->getPost());

                $this->_redirectToEdit();
            }
        }
    }

    public function deleteAction()
    {

        try {
            $asset      = $this->_initAsset();
            $assetImage = $this->_helper->deleteImage($asset['image']);

            $asset->delete();

            $this->_getSession()->addSuccess($this->_helper->__('Asset was successfully deleted'));
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            Mage::log("FROM CLASS " . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
            Mage::log($e->getMessage());

            $this->_getSession()->addError($e->getMessage());
            $this->_redirectToEdit();
        }
    }

    // TODO //
    // PROBLEMA
    // Crie uma tabela para salvar
    // a ordem dos assets para cada slide
    // por exemplo:
    // se mais de um slide usa o mesmo
    // asset quando reorganizando a ordem do
    // asset todos os slides que usam aquele
    // asset serao afetados !!!!

    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */
    public function gridAction()
    {
        $tiles_model    = Mage::getModel('ash_slideshow/asset');
        $post_data      = Mage::app()->getRequest()->getPost();
        $post_data      = $this->getRequest()->getPost();

        if(isset($post_data) and !empty($post_data)) {
            $tiles_model->update_asset_order($post_data);
        }

        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ash_slideshow/adminhtml_asset_grid')->toHtml()
        );
    }

    protected function _redirectToEdit()
    {
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        return;
    }

}
