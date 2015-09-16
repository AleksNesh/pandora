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
class Altima_Lookbookslider_Block_Content_Addtocart extends Mage_Core_Block_Template {

    /**
     * Retrieves url for form submitting:
     * some objects can use setSubmitRouteData() to set route and params for form submitting,
     * otherwise default url will be used
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getSubmitUrl($product, $additional = array())
    {
        $submitRouteData = $this->getData('submit_route_data');

        if ($submitRouteData) {
            $route = $submitRouteData['route'];
            $params = isset($submitRouteData['params']) ? $submitRouteData['params'] : array();
            $submitUrl = $this->getUrl($route, array_merge($params, $additional));
        } else {
            $submitUrl = $this->getAddToCartUrl($product, $additional);
        }
        return $submitUrl;
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {

	if ($this->isMageEnterprise()){
	   $addUrlKey = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;

	   $addUrlValue = Mage::getUrl('*/*/*', array('_use_rewrite' => true, '_current' => true));
	   $additional[$addUrlKey] = Mage::helper('core')->urlEncode($addUrlValue);
	   return $this->helper('checkout/cart')->getAddUrl($product, $additional);
	}
	else
	{
          if (!$product->getTypeInstance(true)->hasRequiredOptions($product)) {
            return $this->helper('checkout/cart')->getAddUrl($product, $additional);
          }
          $additional = array_merge(
            $additional,
            array(Mage_Core_Model_Url::FORM_KEY => Mage::getSingleton('core/session')->getFormKey())
          );

          if (!isset($additional['_escape'])) {
            $additional['_escape'] = true;
          }
          if (!isset($additional['_query'])) {
            $additional['_query'] = array();
          }
          $additional['_query']['options'] = 'cart';
	}
        return $this->getProductUrl($product, $additional);
    }
    
    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = array())
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }
        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     *
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        return false;
    }
        
    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param null|Mage_Catalog_Model_Product $product
     * @return int|float
     */
    public function getProductDefaultQty($product = null)
    {
        if (!$product) {
            $product = $this->getProduct();
        }

        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }
    
    /**
     * Gets minimal sales quantity
     *
     * @param Mage_Catalog_Model_Product $product
     * @return int|null
     */
    public function getMinimalQty($product)
    {
        $stockItem = $product->getStockItem();
        if ($stockItem) {
            return ($stockItem->getMinSaleQty()
            && $stockItem->getMinSaleQty() > 0 ? $stockItem->getMinSaleQty() * 1 : null);
        }
        return null;
    }
    
    /**
     * Return model instance
     *
     * @param string $className
     * @param array $arguments
     * @return Mage_Core_Model_Abstract
     */
    protected function _getSingletonModel($className, $arguments = array())
    {
        return Mage::getSingleton($className, $arguments);
    }

    /**
    * True if the version of Magento currently being rune is Enterprise Edition
    */
    public function isMageEnterprise() {
    	return Mage::getConfig ()->getModuleConfig ( 'Enterprise_Enterprise' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_AdminGws' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Checkout' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Customer' );
    }
}