<?php

/**
 * Simple module for extending core EM_Megamenupro module
 *
 * @category    Pan
 * @package     Pan_Megamenupro
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */

include_once("EM/Megamenupro/controllers/Adminhtml/MenueditorController.php");

class Pan_Megamenupro_Adminhtml_MenueditorController extends EM_Megamenupro_Adminhtml_MenueditorController
{
    /**
     * Overridden to decode the JSON encoded string
     *
     * @return string
     */
    public function parsecodeAction()
    {
        $param = json_decode($this->getRequest()->getParam('menu'), true);

        if(isset($param)) {
            try {
                foreach($param as $k=>$v){
                    if($v['type'] == 'text') {
                        $param[$k]['text'] = base64_encode($v['text']);
                    }
                }

                $data = serialize($param);
            } catch (Exception $e) {
                Mage::log('FROM CLASS ' . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
                Mage::log($e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        } else {
            $data = "";
        }

        echo $data;
        exit;
    }

    /**
     * Overridden to check if data is serialized or not to prevent losing of menus
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('filename');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //  (file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(false);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS ;
                    $uploader->save($path, $_FILES['filename']['name'] );

                } catch (Exception $e) {
                    Mage::log('FROM CLASS ' . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
                    Mage::log($e->getMessage());
                }

                //this way the name is saved in DB
                $data['filename'] = $_FILES['filename']['name'];
            }

            /**
             *
             * START AAI HACK
             *
             * check if data is serialized or not to prevent losing of menus
             *
             */
            if (Mage::helper('megamenupro')->isSerialized($data['content'])) {
                $model = Mage::getModel('megamenupro/megamenupro');
                $model->setData($data)
                      ->setId($this->getRequest()->getParam('id'));

                try {
                    if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                        $model->setCreatedTime(now())
                            ->setUpdateTime(now());
                    } else {
                        $model->setUpdateTime(now());
                    }

                    $model->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('megamenupro')->__('Item was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);

                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/megamenupro/');
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('megamenupro')->__('Unable to save menu because data is possibly corrupted! Reverted back to previously saved data.'));
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                // $this->_redirect('*/megamenupro/');
                return;
            }
            /**
             *
             * END AAI HACK
             *
             */
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('megamenupro')->__('Unable to find item to save'));
        $this->_redirect('*/megamenupro/');
    }
}
