<?php

class Magestore_Giftwrap_Adminhtml_GiftwrapController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController(
                        $this)) {
            return;
        }
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();
        $this->_setActiveMenu('catalog/giftwrap');
        $this->_addBreadcrumb(Mage::helper('giftwrap')->__('Manage Gift Boxes'), Mage::helper('giftwrap')->__('Manage Gift Boxes'));
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController(
                        $this)) {
            return;
        }
        try {
            $id = $this->getRequest()->getParam('id');
            $store_id = $this->getRequest()->getParam('store', 0);
            $model = Mage::getModel('giftwrap/giftwrap')->getStoreGiftwrap($id, $store_id);
            $this->getRequest()->setParam('id', $model->getId());
            $this->getRequest()->setParam('store', $store_id);
            $_SESSION['old_store_id'] = 0;
            Mage::register('giftwrap_data', $model);
            $this->loadLayout();
            $this->_setActiveMenu('catalog/giftwrap');
            if ($model->getId()) {
                $breadcrumbTitle = Mage::helper('giftwrap')->__('Edit Gift Box');
                $breadcrumbLabel = $breadcrumbTitle;
            } else {
                $breadcrumbTitle = Mage::helper('giftwrap')->__('New Gift Box');
            }
            $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);
            //restore data
            if ($values = $this->_getSession()->getData(
                    'giftwrap_form_data', true)) {
                $model->addData($values);
            }
            $content = $this->getLayout()
                    ->createBlock('giftwrap/adminhtml_giftwrap_edit', 'giftwrap_edit')
                    ->setEditMode($model->getId() > 0);
            $this->_addContent($content);
            $this->renderLayout();
            Mage::getSingleton('core/session')->addSuccess('Item was successfully saved');
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError('Item was save error');
        }
    }

    public function saveAction() {
        $store_id = $this->getRequest()->getParam('store', 0);
        //zend_debug::dump($store_id);die();
        $request = $this->getRequest();
        $giftwrap = Mage::getModel('giftwrap/giftwrap');
        if ($id = (int) $request->getParam('id')) {
            $giftwrap->load($id);
        }
        try {
            $image = '';
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('image');
                    $uploader->setAllowedExtensions(
                            array('jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'png', 'PNG'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . DS . 'giftwrap';
                    $uploader->save($path, $_FILES['image']['name']);
                } catch (Exception $e) {
                    
                }
                $image = $_FILES['image']['name'];
            } elseif ($giftwrap->getImage() != '') {
                $image = $giftwrap->getImage();
            }
            $post = $request->getPost();
            if (isset($post['image']['delete']) && $post['image']['delete'] == 1) {
                $image = '';
            }
            if (isset($image) && ($image != '')) {
                try {
                    $path = Mage::getBaseDir('media') . DS . 'giftwrap';
                    $fileImg = new Varien_Image($path . DS . $image);
                    $fileImg->keepAspectRatio(true);
                    $fileImg->keepFrame(true);
                    $fileImg->keepTransparency(true);
                    $fileImg->constrainOnly(false);
                    $fileImg->backgroundColor(array(255, 255, 255));
                    $fileImg->resize(200, 200);
                    $fileImg->save($path . DS . $image, null);
                } catch (Exception $e) {
                    
                }
            }
            if (!$this->getRequest()->getParam('id')) {
                $giftwrap->setTitle($request->getParam('title'))
                        ->setPrice($request->getParam('price'))
                        ->setImage($image)
                        ->setStatus($request->getParam('status'))
                        ->setCharacter($request->getParam('character'))
                        ->setPersonalMessage($request->getParam('personal_message'))
                        ->setSortOrder($request->getParam('sort_order'))
                        ->setStoreId(0);
                $giftwrap->save();
                $giftwrap->load($giftwrap->getId())
                        ->setOptionId($giftwrap->getId())
                        ->save();
                $optionId = $giftwrap->getId();
                $id = $giftwrap->getId();
                $store_id = 0;
                $giftwrap->setId(null);
                foreach (Mage::getModel('core/store')->getCollection() as $store) {
                    $giftwrap->setTitle($request->getParam('title'))
                            ->setPrice($request->getParam('price'))
                            ->setImage($image)
                            ->setStatus($request->getParam('status'))
                            ->setCharacter($request->getParam('character'))
                            ->setPersonalMessage(
                                    $request->getParam('personal_message'))
                            ->setSortOrder($request->getParam('sort_order'))
                            ->setStoreId($store->getId())
                            ->setOptionId($optionId);
                    $giftwrap->save();
                    $giftwrap->setId(null);
                }
            } else {
                if ($store_id != 0) {
                    $giftwrapModel = Mage::getModel('giftwrap/giftwrap')->load(
                            $this->getRequest()
                                    ->getParam('id'));
                    $giftwrapModel->setTitle($request->getParam('title'))
                            ->setPrice($request->getParam('price'))
                            ->setImage($image)
                            ->setStatus($request->getParam('status'))
                            ->setCharacter($request->getParam('character'))
                            ->setPersonalMessage(
                                    $request->getParam('personal_message'))
                            ->setSortOrder($request->getParam('sort_order'))
                            ->setDefaultTitle($request->getParam('default_title'))
                            ->setDefaultPrice($request->getParam('default_price'))
                            ->setDefaultImage($request->getParam('default_image'))
                            ->setDefaultStatus($request->getParam('default_status'))
                            ->setDefaultCharacter(
                                    $request->getParam('default_character'))
                            ->setDefaultPersonalMessage(
                                    $request->getParam('default_personal_message'))
                            ->setDefaultSortOrder(
                                    $request->getParam('default_sort_order'));
                    $giftwrapModel->save();
                    $arrFielName = array(0 => 'title', 1 => 'price',
                        2 => 'character', 3 => 'image', 4 => 'personal_message',
                        5 => 'status', 6 => 'sort_order');
                    $giftwrapDefault = Mage::getModel('giftwrap/giftwrap')->getCollection()
                            ->addFieldToFilter('store_id', '0')
                            ->addFieldToFilter('option_id', $giftwrapModel->getOptionId())
                            ->getFirstItem();
                    $test = array();
                    $count = 0;
                    foreach ($arrFielName as $fielname) {
                        if ($giftwrapModel->getData('default_' . $fielname) ==
                                '1') {
                            $test[] = $fielname;
                            $count++;
                            $giftwrapModel->setData($fielname, $giftwrapDefault->getData($fielname));
                        }
                    }
                    // var_dump($test);die();
                    $id = $giftwrapModel->getId();
                    if ($count > 0) {
                        $giftwrapModel->save();
                        $giftwrapModel->setId(null);
                    }
                } else {// all store = 0
                    $giftwrapModel = Mage::getModel('giftwrap/giftwrap')->load(
                            $this->getRequest()
                                    ->getParam('id'));
                    //zend_debug::dump($giftwrapModel);die();
                    $giftwrapModel->setTitle($request->getParam('title'))
                            ->setPrice($request->getParam('price'))
                            ->setImage($image)
                            ->setStatus($request->getParam('status'))
                            ->setCharacter($request->getParam('character'))
                            ->setPersonalMessage(
                                    $request->getParam('personal_message'))
                            ->setSortOrder($request->getParam('sort_order'))
                            ->setDefaultTitle($request->getParam('default_title'))
                            ->setDafeultPrice($request->getParam('default_price'))
                            ->setDefaultImage($request->getParam('default_image'))
                            ->setDefaultStatus($request->getParam('default_status'))
                            ->setDefaultCharacter(
                                    $request->getParam('default_character'))
                            ->setDefaultPersonalMessage(
                                    $request->getParam('default_personal_message'))
                            ->setDefaultSortOrder(
                                    $request->getParam('default_sort_order'));

                    $giftwrapModel->save();

                    // start HoaNTT 
                    $giftwrapCollection = Mage::getSingleton('giftwrap/giftwrap')->getCollection();
                    foreach ($giftwrapCollection as $_giftwrap) {
                        $_giftwrap->setStatus($request->getParam('status'));
                        $_giftwrap->save();
                    }
                    // end HoaNTT - Magestore

                    $arrFielName = array(0 => 'title', 1 => 'price',
                        2 => 'character', 3 => 'image', 4 => 'personal_message',
                        5 => 'status', 6 => 'sort_order');
                    foreach (Mage::getModel('core/store')->getCollection() as $store) {
                        $col = Mage::getModel('giftwrap/giftwrap')->getCollection()
                                ->addFieldToFilter('store_id', $store->getId())
                                ->addFieldToFilter('option_id', $giftwrapModel->getOptionId())
                                ->getFirstItem();
                        $giftwrapStore = Mage::getModel('giftwrap/giftwrap')->load(
                                $col->getId());
                        $count = 0;
                        foreach ($arrFielName as $fielname) {
                            if ($giftwrapStore->getData('default_' . $fielname) ==
                                    '1') {
                                $count++;
                                $giftwrapStore->setData($fielname, $giftwrapModel->getData($fielname));
                            }
                        }
                        $id = $giftwrapStore->getId();
                        if ($count > 0) {
                            $giftwrapStore->save();
                            $giftwrapStore->setId(null);
                        }
                    }
                }
            }
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $id, 'store' => $store_id));
                return;
            }
            $this->_redirect('*/*');
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(nl2br($e->getMessage()));
            $this->_getSession()->setData('giftwrap_form_data', $this->getRequest()
                            ->getParams());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('adminhtml')->__(
                            'Error while saving this giftwrap style. Please try again later.'));
            $this->_getSession()->setData('giftwrap_form_data', $this->getRequest()
                            ->getParams());
        }
        $this->_forward('new');
    }

    public function deleteAction() {
        $store_id = $this->getRequest()->getParam('store', 0);
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('giftwrap/giftwrap');
                if ($store_id == 0) {
                    $collection = $model->getCollection()->addFieldToFilter(
                            'option_id', $this->getRequest()
                                    ->getParam('id'));
                    foreach ($collection as $item) {
                        $item->delete();
                    }
                } else {
                    $model->setId($this->getRequest()
                                    ->getParam('id'))
                            ->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                        $e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()
                            ->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $store_id = $this->getRequest()->getParam('store', 0);
        $giftwrapIds = $this->getRequest()->getParam('giftwrap');
        if (!is_array($giftwrapIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($giftwrapIds as $giftwrapId) {
                    if ($store_id == 0) {
                        $collection = Mage::getModel('giftwrap/giftwrap')
                                ->getCollection()
                                ->addFieldToFilter('option_id', $giftwrapId);
                        foreach ($collection as $item) {
                            $item->delete();
                        }
                    } else {
                        $giftwrap = Mage::getModel('giftwrap/giftwrap')->load(
                                $giftwrapId);
                        $giftwrap->delete();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($giftwrapIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                        $e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    //loki add status mass
    public function massStatusAction() {
        $store_id = $this->getRequest()->getParam('store', 0);
        $giftwrapIds = $this->getRequest()->getParam('giftwrap');
        if (!is_array($giftwrapIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                // start HoaNTT
                foreach ($giftwrapIds as $giftwrapId) {
                    if ($store_id != 0) {
                        Mage::getSingleton('giftwrap/giftwrap')
                                ->load($giftwrapId)
                                ->setStatus($this->getRequest()->getParam('status'))
                                ->setIsMassupdate(true)
                                ->save();
                    } else {
                        $giftwraps = Mage::getSingleton('giftwrap/giftwrap')
                                        ->getCollection()->addFieldToFilter('option_id', $giftwrapId);
                        foreach ($giftwraps as $item) {
                            $item->setStatus($this->getRequest()->getParam('status'))
                                    ->setIsMassupdate(true)
                                    ->save();
                        }
                    }
                }// end HoaNTT
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($giftwrapIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    //end
}
