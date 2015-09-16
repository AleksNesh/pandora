<?php

class Magestore_Giftwrap_Adminhtml_GiftcardController extends Mage_Adminhtml_Controller_Action {

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();
        $this->_setActiveMenu('catalog/giftwrap');
        $this->_addBreadcrumb(Mage::helper('giftwrap')->__('Manage Gift Cards'), Mage::helper('giftwrap')->__('Manage Gift Cards'));
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        try {
            $id = $this->getRequest()->getParam('id');
            $store_id = $this->getRequest()->getParam('store', 0);
            $model = Mage::getModel('giftwrap/giftcard')->getStoreGiftcard($id, $store_id);

            $this->getRequest()->setParam('id', $model->getId());
            $this->getRequest()->setParam('store', $store_id);
            $_SESSION['old_store_id'] = 0;

            Mage::register('giftcard_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('catalog/giftwrap');

            if ($model->getId()) {
                $breadcrumbTitle = Mage::helper('giftwrap')->__('Edit Gift Card') . ' ' . $model->getName();
                $breadcrumbLabel = $breadcrumbTitle;
            } else {
                $breadcrumbLabel = Mage::helper('giftwrap')->__('New Gift Card');
            }

            $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

            //restore data
            if ($values = $this->_getSession()->getData('giftcard_form_data', true)) {
                $model->addData($values);
            }
            $content = $this->getLayout()
                    ->createBlock('giftwrap/adminhtml_giftcard_edit', 'giftcard_edit')
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
        $request = $this->getRequest();
        $giftcard = Mage::getModel('giftwrap/giftcard');
        if ($id = (int) $request->getParam('id')) {
            $giftcard->load($id);
        }
        try {
            $image = '';
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
                try {
                    $uploader = new Varien_File_Uploader('image');
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $path = Mage::getBaseDir('media') . DS . 'giftwrap' . DS . 'giftcard';
                    $uploader->save($path, $_FILES['image']['name']);
                } catch (Exception $e) {
                    
                }
                $image = $_FILES['image']['name'];
            } elseif ($giftcard->getImage() != '') {
                $image = $giftcard->getImage();
            }
            $post = $request->getPost();
            if (isset($post['image']['delete']) && $post['image']['delete'] == 1) {
                $image = '';
            }

            if (isset($image) && ($image != '')) {
                try {
                    $path = Mage::getBaseDir('media') . DS . 'giftwrap' . DS . 'giftcard';
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
                $giftcard->setName($request->getParam('name'))
                        ->setPrice($request->getParam('price'))
                        ->setImage($image)
                        ->setCharacter($request->getParam('character'))
                        ->setStatus($request->getParam('status'))
                        ->setMessage($request->getParam('message'))
                        ->setStoreId(0)
                ;
                $giftcard->save();
                $giftcard->load($giftcard->getId())
                        ->setOptionId($giftcard->getId())
                        ->save()
                ;
                $optionId = $giftcard->getId();
                $id = $giftcard->getId();
                $store_id = 0;
                $giftcard->setId(null);
                foreach (Mage::getModel('core/store')->getCollection() as $store) {
                    $giftcard->setName($request->getParam('name'))
                            ->setPrice($request->getParam('price'))
                            ->setImage($image)
                            ->setStatus($request->getParam('status'))
                            ->setMessage($request->getParam('message'))
                            ->setCharacter($request->getParam('character'))
                            ->setStoreId($store->getId())
                            ->setOptionId($optionId)
                    ;
                    $giftcard->save();
                    $giftcard->setId(null);
                }
            } else {
                if ($store_id != 0) {

                    $giftcardModel = Mage::getModel('giftwrap/giftcard')->load($this->getRequest()->getParam('id'));
                    $giftcardModel->setName($request->getParam('name'))
                            ->setPrice($request->getParam('price'))
                            ->setImage($image)
                            ->setStatus($request->getParam('status'))
                            ->setMessage($request->getParam('message'))
                            ->setCharacter($request->getParam('character'))
                            ->setDefaultName($request->getParam('default_name'))
                            ->setDefaultCharacter($request->getParam('default_character'))
                            ->setDefaultPrice($request->getParam('default_price'))
                            ->setDefaultImage($request->getParam('default_image'))
                            ->setDefaultStatus($request->getParam('default_status'))
                            ->setDefaultMessage($request->getParam('default_message'))
                    ;
                    $giftcardModel->save();
                    $arrFielName = array(0 => 'name', 1 => 'price', 2 => 'image', 3 => 'message', 4 => 'status');
                    $giftcardDefault = Mage::getModel('giftwrap/giftcard')
                            ->getCollection()
                            ->addFieldToFilter('store_id', '0')
                            ->addFieldToFilter('option_id', $giftcardModel->getOptionId())
                            ->getFirstItem()
                    ;
                    $test = array();
                    $count = 0;
                    foreach ($arrFielName as $fielname) {
                        if ($giftcardModel->getData('default_' . $fielname) == '1') {
                            $test[] = $fielname;
                            $count++;
                            $giftcardModel->setData($fielname, $giftcardDefault->getData($fielname));
                        }
                    }
                    // var_dump($test);die();
                    $id = $giftcardModel->getId();
                    if ($count > 0) {
                        $giftcardModel->save();
                        $giftcardModel->setId(null);
                    }
                } else {
                    $giftcardModel = Mage::getModel('giftwrap/giftcard')->load($this->getRequest()->getParam('id'));
                    $giftcardModel->setName($request->getParam('name'))
                            ->setPrice($request->getParam('price'))
                            ->setImage($image)
                            ->setStatus($request->getParam('status'))
                            ->setMessage($request->getParam('message'))
                            ->setCharacter($request->getParam('character'))
                            ->setSortOrder($request->getParam('sort_order'))
                            ->setDefaultName($request->getParam('default_name'))
                            ->setDafeultPrice($request->getParam('default_price'))
                            ->setDefaultImage($request->getParam('default_image'))
                            ->setDefaultCharacter($request->getParam('default_character'))
                            ->setDefaultStatus($request->getParam('default_status'))
                            ->setDefaultMessage($request->getParam('default_message'))
                    ;
                    $giftcardModel->save();
                    
                    // HoaNTT
                    $giftcardCollection = Mage::getSingleton('giftwrap/giftcard')->getCollection();
                    foreach ($giftcardCollection as $_giftcard) {
                        $_giftcard->setStatus($request->getParam('status'));
                    }
                    
                    // end HoaNTT - Magestore
                    
                    $arrFielName = array(0 => 'name', 1 => 'price', 2 => 'image', 3 => 'message', 4 => 'status', 5 => 'character');
                    foreach (Mage::getModel('core/store')->getCollection() as $store) {
                        $col = Mage::getModel('giftwrap/giftcard')
                                ->getCollection()
                                ->addFieldToFilter('store_id', $store->getId())
                                ->addFieldToFilter('option_id', $giftcardModel->getOptionId())
                                ->getFirstItem()
                        ;
                        $giftcardStore = Mage::getModel('giftwrap/giftcard')->load($col->getId());
                        $count = 0;
                        foreach ($arrFielName as $fielname) {
                            if ($giftcardStore->getData('default_' . $fielname) == '1') {
                                $count++;
                                $giftcardStore->setData($fielname, $giftcardModel->getData($fielname));
                            }
                        }
                        //$id = $giftcardStore->getId();
                        if ($count > 0) {
                            $giftcardStore->save();
                            $giftcardStore->setId(null);
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
            $this->_getSession()->setData('giftcard_form_data', $this->getRequest()->getParams());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, Mage::helper('adminhtml')->__('Error while saving this gift card. Please try again later.'));
            $this->_getSession()->setData('giftcard_form_data', $this->getRequest()->getParams());
        }
        $this->_forward('new');
    }

    public function deleteAction() {
        $store_id = $this->getRequest()->getParam('store', 0);
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('giftwrap/giftcard');
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
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $store_id = $this->getRequest()->getParam('store', 0);
        $giftcardIds = $this->getRequest()->getParam('giftcard');
        if (!is_array($giftcardIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($giftcardIds as $giftcardId) {
                    if ($store_id == 0) {
                        $collection = Mage::getModel('giftwrap/giftcard')
                                ->getCollection()
                                ->addFieldToFilter('option_id', $giftcardId);
                        foreach ($collection as $item) {
                            $item->delete();
                        }
                    } else {
                        $giftcard = Mage::getModel('giftwrap/giftcard')->load(
                                $giftcardId);
                        $giftcard->delete();
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($giftcardIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    //loki add status mass
    public function massStatusAction() {
        $giftcardIds = $this->getRequest()->getParam('giftcard');
        $store_id = $this->getRequest()->getParam('store', 0);
        if (!is_array($giftcardIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                //start HoaNTT
                foreach ($giftcardIds as $giftcardId) {
                      if ($store_id != 0) {
                        Mage::getSingleton('giftwrap/giftcard')
                                ->load($giftcardId)
                                ->setStatus($this->getRequest()->getParam('status'))
                                ->setIsMassupdate(true)
                                ->save();
                    } else {
                        $giftcards = Mage::getSingleton('giftwrap/giftcard')
                                        ->getCollection()->addFieldToFilter('option_id', $giftcardId);
                        foreach ($giftcards as $item) {
                            $item->setStatus($this->getRequest()->getParam('status'))
                                    ->setIsMassupdate(true)
                                    ->save();
                        }
                    }
                } // end HoaNTT
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($giftcardIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    //end
}
