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
class Altima_Lookbookslider_Adminhtml_SlideController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('cms');
		
		return $this;
	}   
 
	public function indexAction() {
	    $slider_id = $this->getRequest()->getParam('slider_id');
        if (!$slider_id) {
           Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Slide id not set'));
           $this->_redirect('lookbookslider/adminhtml_lookbookslider/index');         
        } 
        else
        {
            Mage::getSingleton('adminhtml/session')->setSliderId($slider_id);        
            $slider  = Mage::getModel('lookbookslider/lookbookslider')->load($slider_id);
            if (!$slider->hasData()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Slide with id=%s not exists', $slider_id));
                $this->_redirect('lookbookslider/adminhtml_lookbookslider/index');  
            }
            else
            {
                Mage::register('slider_data', $slider);
        		$this->_initAction()
        			->renderLayout();                  
            }
          
        }       

	}

	public function editAction() {
            $slider_id = $this->getRequest()->getParam('slider_id');
            $id = $this->getRequest()->getParam('id');               

    		$model  = Mage::getModel('lookbookslider/slide')->load($id);

    		if ($model->getId() || $id == 0) {
    			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
    			if (!empty($data)) {
    				$model->setData($data);
    			}
                if ($slider_id) {
                   $model->setData('lookbookslider_id', $slider_id); 
                }
    			Mage::register('slide_data', $model);
    
    			$this->loadLayout();
    			$this->_setActiveMenu('CMS');
    
    			$this->_addContent($this->getLayout()->createBlock('lookbookslider/adminhtml_slide_edit'))
    				->_addLeft($this->getLayout()->createBlock('lookbookslider/adminhtml_slide_edit_tabs'));
    
    			$this->renderLayout();
    		} else {
    			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Slide does not exists'));
    			$this->_redirect('*/*/', array('slider_id' => $slider_id));
    		}
	}
 
	public function newAction() {
	    $slider_id = $this->getRequest()->getParam('slider_id');
        $this->_forward('edit', null, null, array('slider_id' => $slider_id));
	}
 
	public function saveAction() {
	    $slider_id = $this->getRequest()->getParam('slider_id');	   
 	if ($data = $this->getRequest()->getPost()) {
			$hotspots = str_replace('\"','"', $data['hotspots']);
			$hotspots = str_replace(']"',']', $hotspots);
			$data['hotspots'] = str_replace('"[','[', $hotspots);		
			$model = Mage::getModel('lookbookslider/slide');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
			 
                 if ($model->getId() && isset($data['identifier_create_redirect']))
                 {
                        $model->setData('save_rewrites_history', (bool)$data['identifier_create_redirect']);
                 }
             
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('lookbookslider')->__('Slide was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId(), 'slider_id' => $model->getData('lookbookslider_id')));
					return;
				}
				$this->_redirect('*/*/', array('slider_id' => $model->getData('lookbookslider_id')));
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'),'slider_id' => $model->getData('lookbookslider_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Unable to find slide to save'));
        $this->_redirect('*/*/', array('id' => $slider_id));
	}
 
	public function deleteAction() {
	    $slider_id = $this->getRequest()->getParam('slider_id');	   
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('lookbookslider/slide');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('lookbookslider')->__('Slide was successfully deleted'));
				$this->_redirect('*/*/', array('slider_id' => $slider_id));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'), 'slider_id' => $slider_id));
			}
		}
		$this->_redirect('*/*/', array('slider_id' => $slider_id));
	}

    public function uploadAction()
	{
	       $slider_id = $this->getRequest()->getParam('slider_id');
            
            if ($slider_id) {
                $slider_dimensions = Mage::getModel('lookbookslider/lookbookslider')->load($slider_id)->toArray(array('width','height'));
                
                $upload_dir  = Mage::getBaseDir('media').DS.'lookbookslider'.DS;
    
               if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);
                
                $uploader = Mage::getModel('lookbookslider/fileuploader');
    
                $config_check = $uploader->checkServerSettings();
    
                if ($config_check === true){
                   $result = $uploader->handleUpload($upload_dir, $slider_dimensions); 
                } 
                else
                {
                    $result = $config_check;
                }
            }
            else {
                $result = array('error' => Mage::helper('lookbookslider')->__("File can't be uploaded. Slider_id not set.")); 
            }
            // to pass data through iframe you will need to encode all html tags
            $this->getResponse()->setBody(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
	}

    
    public function getproductAction(){
        	$sku     = $this->getRequest()->getParam('sku');
            $product_id = Mage::getModel('catalog/product')->getIdBySku($sku);
            $status =  Mage::getModel('catalog/product')->load($product_id)->getStatus();
            if ($product_id) {
                if ($status==1) 
                {
                  $result= 1;  
                }
                else
                {
                  $result = "is disabled";  
                }
                
            }
            else
            {
                $result = "doesn't exists"; 
            }
    $this->getResponse()->setBody($result);
    }
    
    public function massDeleteAction() {
        $slideIds = $this->getRequest()->getParam('slide');
        $slider_id = Mage::getSingleton('adminhtml/session')->getSliderId();        
        if(!is_array($slideIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lookbookslider')->__('Please select slide(s)'));
        } else {
            try {
                foreach ($slideIds as $slideId) {
                    $slide = Mage::getModel('lookbookslider/slide')->load($slideId);
                    $slide->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('lookbookslider')->__(
                        'Total of %d record(s) were successfully deleted', count($slideIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('slider_id' => $slider_id));
    }
	
    public function massStatusAction()
    {
        $slideIds = $this->getRequest()->getParam('slide');
        $slider_id = Mage::getSingleton('adminhtml/session')->getSliderId();                
        if(!is_array($slideIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Slide(s)'));                        
        } else {            
            try {
                foreach ($slideIds as $slideId) {
                    $slide = Mage::getSingleton('lookbookslider/slide')
                        ->load($slideId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($slideIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('slider_id' => $slider_id));
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'lookbookslides.csv';
        $content    = $this->getLayout()->createBlock('lookbookslider/adminhtml_slide_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'lookbookslides.xml';
        $content    = $this->getLayout()->createBlock('lookbookslider/adminhtml_slide_grid')
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
}