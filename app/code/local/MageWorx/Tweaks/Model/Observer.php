<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @copyright  Copyright (c) 2010 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Tweaks extension
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Tweaks_Model_Observer
{
    public function setCustomerIsSubscribed($observer)
    {
    	if (Mage::helper('tweaks')->isOnepageCheckoutNewsletterEnabled()) {
	        if ((bool) Mage::getSingleton('customer/session')->getCustomerIsSubscribed()) {
	            $quote = $observer->getEvent()->getQuote();
	            $customer = $quote->getCustomer();

	            if (version_compare(Mage::getVersion(), '1.4.0', '>=')) {
                    $checkoutMethod = $quote->getCheckoutMethod(true);
                } else {
                    $checkoutMethod = $quote->getCheckoutMethod();
                }

	            switch ($checkoutMethod) {
	                case 'register':
	                    $customer->setIsSubscribed(1);
	                    $customer->save();

	                    break;

	                case 'guest':
	                    $session = Mage::getSingleton('core/session');
	                    try {
	                        $status = Mage::getModel('newsletter/subscriber')->subscribe($quote->getBillingAddress()->getEmail());
	                        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
	                            $session->addSuccess(Mage::helper('tweaks')->__('Confirmation request has been sent regarding your newsletter subscription'));
	                        }
	                    } catch (Mage_Core_Exception $e) {
	                        $session->addException($e, Mage::helper('tweaks')->__('There was a problem with the newsletter subscription: %s', $e->getMessage()));
	                    } catch (Exception $e) {
	                        $session->addException($e, Mage::helper('tweaks')->__('There was a problem with the newsletter subscription'));
	                    }
	                    break;
	            }
	            Mage::getSingleton('customer/session')->unsCustomerIsSubscribed();
	        }
    	}
    }

    public function initSingleProductRedirect($observer)
    {
    	if (Mage::helper('tweaks')->isCategorySingleProductRedirectEnable()) {
    		$categoryId = (int) $observer->getEvent()->getControllerAction()->getRequest()->getParam('id');
    		$category = Mage::getSingleton('catalog/category')->load($categoryId);
	        $action = $observer->getEvent()->getControllerAction();
	        if (1 == $category->getProductCount()) {
	            $url = $category->getProductCollection()->getFirstItem()->getProductUrl();
	            return $action->getResponse()->setRedirect($url);
	        }
    	}
    }

    public function initProductDescriptionProcess($observer)
    {
        if (!Mage::helper('tweaks')->isProductDescriptionTemplatesEnable()){
            return;
        }
        
        $output = $observer->getEvent()->getHelper();
        $output->addHandler('productAttribute', Mage::helper('tweaks/output'));
    }
            
    public function initProductCollectionBestsellersOrder($observer) {
        $request = Mage::app()->getRequest();
        $order = $request->getParam('order');
        $dir = ($request->getParam('dir') == 'desc') ? 'asc' : 'desc';
        if (Mage::helper('tweaks')->isOrderByBestsellersEnabled() && $order == 'bestsellers') {
            $storeId = Mage::app()->getStore()->getStoreId();
            $observer->setStoreId($storeId);
            
            $observer->getCollection()->clear();
            $select = $observer->getCollection()->getSelect();
            
            $subSelect = clone $select;
            $subSelect->reset()
                ->from(array('t1' => 'sales_flat_order_item'), array('t1.product_id', 'ordered_qty' => 'SUM(t1.qty_ordered)'))
                ->group('t1.product_id');
                
            $select->joinLeft(array('order_items' => $subSelect), 'e.entity_id=order_items.product_id', array('order_items.ordered_qty'))
                ->order('order_items.ordered_qty '.$dir);
            
            //$sql = $select->reset(Zend_Db_Select::ORDER)
            //                ->joinLeft(array('order_items' => 'sales_flat_order_item'), 'e.entity_id=order_items.product_id', array('ordered_qty' => 'SUM(order_items.qty_ordered)'))
            //                ->group('e.entity_id')->assemble();

            //$sql = str_ireplace('`price_index`.`price`, `price_index`.`tax_class_id`,', '', $sql);
            //$select->reset()->from(array('e' => new Zend_Db_Expr('(' . $sql . ')')), '*')->order('ordered_qty ' . $dir);
        }
    }
    
    
    
    public function initProductCollectionNewestOrder($observer) {
        $request = Mage::app()->getRequest();
        $order = $request->getParam('order');
        $dir = ($request->getParam('dir') == 'desc') ? 'asc' : 'desc';

        if (Mage::helper('tweaks')->isOrderByNewestProductEnabled() && $order == 'newest') {
            $storeId = Mage::app()->getStore()->getStoreId();
            
            $observer->getCollection()->clear();
            
            $observer->setStoreId($storeId)
                    ->getCollection()
                    ->getSelect()
                    ->reset(Zend_Db_Select::ORDER)
                    ->order('created_at ' . $dir);
        }
    }
    
    public function initProductCollectionPriceUpOrder($observer)
    {
    	$request = Mage::app()->getRequest();
		$order = $request->getParam('order');
		
		if (Mage::helper('tweaks')->isOrderByPriceUpEnabled() && $order == 'price_up'){
			
			$storeId = Mage::app()->getStore()->getStoreId();
		
			if(version_compare(Mage::getVersion(), '1.4.0', '<' )){
				$observer->setStoreId($storeId)
	       				 ->getCollection()
	       				 ->getSelect()
	       				 ->reset(Zend_Db_Select::ORDER)
	       				 ->joinLeft(array('_price_order_table' => 'catalogindex_price'),
	       				 			'_price_order_table.entity_id = e.entity_id',
	       				 			array('value' => '_price_order_table.value'))
	       				 ->group('entity_id')
	       				 ->order('value asc');
	       		
			} else {
				
	       		$observer->setStoreId($storeId)
	       				 ->getCollection()
	       				 ->getSelect()
	       				 ->reset(Zend_Db_Select::ORDER)
	       				 ->order('final_price asc');
				
			}
		}
		
    }
    
    public function initProductCollectionPriceDownOrder($observer)
    {
    	$request = Mage::app()->getRequest();
		$order = $request->getParam('order');
		
		if (Mage::helper('tweaks')->isOrderByPriceUpEnabled() && $order == 'price_down'){
			
			$storeId = Mage::app()->getStore()->getStoreId();
		
			if(version_compare(Mage::getVersion(), '1.4.0', '<' )){
				
				$observer->setStoreId($storeId)
	       				 ->getCollection()
	       				 ->getSelect()
	       				 ->reset(Zend_Db_Select::ORDER)
	       				 ->joinLeft(array('_price_order_table' => 'catalogindex_price'),
	       				 			'_price_order_table.entity_id = e.entity_id',
	       				 			array('value' => '_price_order_table.value'))
	       				 ->group('entity_id')
	       				 ->order('value desc');
				
			} else {
				
	       		$observer->setStoreId($storeId)
	       				 ->getCollection()
	       				 ->getSelect()
	       				 ->reset(Zend_Db_Select::ORDER)
	       				 ->order('final_price desc');
				
			}
		}
				
    }
    
    public function initProductCollectionReviewOrder($observer)
    {
    	$request = Mage::app()->getRequest();
		$order = $request->getParam('order');
		$dir = ($request->getParam('dir') == 'desc') ? 'asc' : 'desc';
		
		if (Mage::helper('tweaks')->isOrderByPriceDownEnabled() && $order == 'review'){
			$storeId = Mage::app()->getStore()->getStoreId();
       		$observer->setStoreId($storeId)
       				 ->getCollection()
       				 ->getSelect()
       				 ->reset(Zend_Db_Select::ORDER)
       				 ->joinLeft(array('rating' => 'rating_option_vote'),
       				 			'e.entity_id=rating.entity_pk_value',
       				 			array('average_review' => 'SUM(rating.percent)/COUNT(rating.vote_id)'))
       				 ->group('e.entity_id')
       				 ->order('average_review '.$dir);
		}
    }
}