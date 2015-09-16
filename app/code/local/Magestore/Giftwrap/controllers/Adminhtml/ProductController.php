<?php
class Magestore_Giftwrap_Adminhtml_ProductController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('catalog/giftwrap')
			->_addBreadcrumb(Mage::helper('giftwrap')->__('Manage Wrappable Products'), 
							Mage::helper('giftwrap')->__('Manage Wrappable Products'));
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){return;}
		$this->_initAction()
			->renderLayout();
	}
	
	public function productgridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('giftwrap/adminhtml_product_grid')->toHtml()
        );
    }
	
	public function enableAction(){
        $storeId = $this->getRequest()->getParam('store',0);
		$productIds = $this->getRequest()->getParam('product');
		try{
//			foreach($productIds as $productId){
//				Mage::getModel('catalog/product')
//						->load($productId)
//						->setGiftwrap(Magestore_Giftwrap_Model_Giftwrap::STATUS_ENABLED)
//						->save();
//			}
            Mage::getSingleton('catalog/product_action')->updateAttributes($productIds,array('giftwrap' => Magestore_Giftwrap_Model_Giftwrap::STATUS_ENABLED),$storeId);
			$this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully enable', count($productIds))
                );
		}catch(Exception $ex){
			Mage::getSingleton('core/session')->addError($ex->getMessage());
		}
		$this->_redirect('*/*/');
	}
	
	public function disableAction(){
		$productIds = $this->getRequest()->getParam('product');
		$giftwrap = Magestore_Giftwrap_Model_Giftwrap::STATUS_DISABLED;
        $storeId = $this->getRequest()->getParam('store',0);
		try{
//			foreach($productIds as $productId){
//				Mage::getModel('catalog/product')
//						->load($productId)
//						->setGiftwrap(Magestore_Giftwrap_Model_Giftwrap::STATUS_DISABLED)
//						->save();
//			}
            Mage::getSingleton('catalog/product_action')->updateAttributes($productIds,array('giftwrap' => $giftwrap),$storeId);
			$this->_getSession()->addSuccess(
                    $this->__('%d product(s) were successfully disabled for wrapping.', count($productIds))
                );
		}catch(Exception $ex){
			Mage::getSingleton('core/session')->addError($ex->getMessage());
		}
		$this->_redirect('*/*/');
	}
}