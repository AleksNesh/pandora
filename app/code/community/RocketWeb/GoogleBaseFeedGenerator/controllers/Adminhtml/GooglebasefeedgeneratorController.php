<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_GoogleBaseFeedGenerator
 * @copyright Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_GoogleBaseFeedGenerator_Adminhtml_GooglebasefeedgeneratorController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_forward('noroute');
    }

    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/rocketweb_googlebasefeedgenerator');
    }

    public function generateAction()
    {
        $this->loadLayout('popup');
        $this->_addContent(
            $this->getLayout()->createBlock('googlebasefeedgenerator/adminhtml_system_process', 'google_base_gen_feed')
        );
        $this->renderLayout();
    }

    public function downloadFeedAction()
    {
        try {
            $store = $this->getRequest()->getParam('store');

            $testmode = $this->getRequest()->getParam('testmode', 0);
            $sku = $this->getRequest()->getParam('sku');
            $limit = (int)$this->getRequest()->getParam('limit', 0);
            $offset = (int)$this->getRequest()->getParam('offset', 0);

            $store_code = Mage_Core_Model_Store::DEFAULT_CODE;
            if ($store != "") {
                $store_code = $store;
            }

            try {
                $store_id = Mage::app()->getStore($store_code)->getStoreId();
            } catch (Exception $e) {
                Mage::throwException(sprintf('Store with code \'%s\' doesn\'t exists.', $store_code));
            }
            $Generator = Mage::getSingleton('googlebasefeedgenerator/tools')->addData(array('store_code' => $store_code))->getGenerator($store_id);

            if ($testmode) {
                $Generator->setTestMode(true);
                if ($sku) {
                    $Generator->setTestSku($sku);
                } elseif ($offset >= 0 && $limit > 0) {
                    $Generator->setTestOffset($offset);
                    $Generator->setTestLimit($limit);
                } else {
                    Mage::throwException(sprintf("Invalid parameters for test mode: sku %s or offset %s and limit %s", $sku, $offset, $limit));
                }
            }

            $filePath = $Generator->getFeedPath();
            if (!is_file($filePath) || !is_readable($filePath)) {
                throw new Exception('File %s doesn\'t exist.', $$filePath);
            }

            return $this->_prepareDownloadResponse(
                basename($filePath),
                array('value' => $filePath,
                    'type' => 'filename'),
                //"text/tab-separated-values",
                "text/plain",
                filesize($filePath)
            );
        } catch (Exception $e) {
            $this->_forward('noRoute');
        }
    }

    /**
     * Get tree node (Ajax version)
     * Taken from Mage Adminhtml/Promo/WidgetController
     */
    public function categoriesJsonAction()
    {
        if ($categoryId = (int)$this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            );
        }
    }

    /**
     * Initialize category object in registry
     * Taken from Mage Adminhtml/Promo/WidgetController
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');

        $category = Mage::getModel('catalog/category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    return false;
                }
            }
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);

        return $category;
    }
}