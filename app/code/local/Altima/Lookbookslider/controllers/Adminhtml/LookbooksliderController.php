<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Adminhtml_LookbooksliderController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('cms');
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('lookbookslider/lookbookslider')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('lookbookslider_data', $model);
            Mage::register('current_lookbookslider', $model);
			$this->loadLayout();
			$this->_setActiveMenu('cms');

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
			$this->_addContent($this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_edit'))
				->_addLeft($this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Slider does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {  			
	
			$model = Mage::getModel('lookbookslider/lookbookslider');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
            if (!$data['width'] || !$data['height']) {
			   Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Slider width and height must be set and greater then zero.'));
               Mage::getSingleton('adminhtml/session')->setFormData($data);
               $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'),'slider_id' => $model->getData('lookbookslider_id')));
               return;
            }   
			try {
			    if (!empty($data['categories'])) {
                    $data['categories'] = explode(',',$data['categories']);
                    if (is_array($data['categories'])) {
                        $data['categories'] = array_unique($data['categories']);
                    }
                }
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('lookbookslider')->__('Slider was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Unable to find slider to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('lookbookslider/lookbookslider');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('lookbookslider')->__('Slider was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $lookbooksliderIds = $this->getRequest()->getParam('lookbookslider');
        if(!is_array($lookbooksliderIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Slider(s)'));
        } else {
            try {
                foreach ($lookbooksliderIds as $lookbooksliderId) {
                    $lookbookslider = Mage::getModel('lookbookslider/lookbookslider')->load($lookbooksliderId);
                    $lookbookslider->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($lookbooksliderIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
                        
        $lookbooksliderIds = $this->getRequest()->getParam('lookbookslider');
        if(!is_array($lookbooksliderIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Slider(s)'));
        } else {
            try {
                foreach ($lookbooksliderIds as $lookbooksliderId) {
                    $lookbookslider = Mage::getSingleton('lookbookslider/lookbookslider')
                        ->setIsMassStatus(true)                    
                        ->load($lookbooksliderId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($lookbooksliderIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'lookbookslider.csv';
        $content    = $this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'lookbookslider.xml';
        $content    = $this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    public function categoriesJsonAction()
    {
        $sliderId     = $this->getRequest()->getParam('id');
        $_model  = Mage::getModel('lookbookslider/lookbookslider')->load($sliderId);
		
        Mage::register('lookbookslider_data', $_model);
        Mage::register('current_lookbookslider', $_model);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('lookbookslider/adminhtml_lookbookslider_edit_tab_category')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
}