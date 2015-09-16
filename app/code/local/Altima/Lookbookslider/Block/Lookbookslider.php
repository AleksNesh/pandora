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
class Altima_Lookbookslider_Block_Lookbookslider extends Mage_Core_Block_Template
{
    protected $_position = null;
    protected $_isActive = 1;
    protected $_collection;
    
    public function _getCollection($position = null) {
        if ($this->_collection) {
            return $this->_collection;
        }

      //  $storeId = Mage::app()->getStore()->getId();
        $this->_collection = Mage::getModel('lookbookslider/lookbookslider')->getCollection()
                ->addEnableFilter($this->_isActive);
       /* if (!Mage::app()->isSingleStoreMode()) {
            $this->_collection->addStoreFilter($storeId);
        }*/

        if (Mage::registry('current_category') && !Mage::registry('current_product')) {
            $_categoryId = Mage::registry('current_category')->getId();
            $this->_collection->addCategoryFilter($_categoryId);
        } elseif (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
            $_pageId = Mage::getBlockSingleton('cms/page')->getPage()->getPageId();
            $this->_collection->addPageFilter($_pageId);
        }
        else
        {
            return false;
        }

        if ($position) {
            $this->_collection->addPositionFilter($position);
        } elseif ($this->_position) {
            $this->_collection->addPositionFilter($this->_position);
        }
        return $this->_collection;
    }
    
    public function _getSlidesCollection($slider_id = null) {
        if (!$slider_id) {
            $collection = null;
        }
        else
        {
            $collection = Mage::getModel('lookbookslider/slide')
                ->getCollection()
                ->addFieldToFilter('lookbookslider_id', $slider_id)
                ->addEnableFilter($this->_isActive)
                ->setOrder('position', 'ASC');
        }
        return $collection;
    }
    
    public function getCacheKey()
    {
        if (!$this->hasData('cache_key')) {
            if (Mage::registry('current_category') && !Mage::registry('current_product')) {
                $_categoryId = Mage::registry('current_category')->getId();
                $cacheKey = 'POSITION_'.$this->_position.'LAYOUT_'.$this->getNameInLayout().'_CATEGORY'.$_categoryId;
            } elseif (Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
                $_pageId = Mage::getBlockSingleton('cms/page')->getPage()->getPageId();
                $cacheKey = 'POSITION_'.$this->_position.'LAYOUT_'.$this->getNameInLayout().'_PAGE'.$_pageId;
            }
        	$this->setCacheKey($cacheKey);
        }
        return $this->getData('cache_key');
    }

    /**
     * Change SKU to product information (link data) into Json array
     *
     * @param json array $array
     * @return array
     */ 
    public function getHotspotsWithProductDetails($slide){
        $helper = Mage::helper('lookbookslider');
        $hotspots = $slide->getHotspots();
        if ($hotspots=='') return '';
	    $decoded_array = json_decode($hotspots,true);
        $img_width = $slide->getWidth();
        $hotspot_icon  = $helper->getHotspotIcon();
        $hotspot_icon_path  = $helper->getHotspotIconPath();
	    $icon_dimensions = $helper->getImageDimensions($hotspot_icon_path);
        $_coreHelper = Mage::helper('core');
		 
        foreach($decoded_array as $key => $value){
		 
           $product_details = null; 
           if ($decoded_array[$key]['sku']!='') {
                $product_details = Mage::getModel('catalog/product')->loadByAttribute('sku',$decoded_array[$key]['sku']);
                if($product_details){
                  $product_details_full = Mage::getModel('catalog/product')->load($product_details->getId()); 
                }else{
                  $decoded_array[$key]['text']=$this->__("Product with SKU %s doesn't exist",$decoded_array[$key]['sku']);
                }
           }	

     		$html_content = '';
    		if (!isset($icon_dimensions['error'])) {
                	   $html_content .= '<img class="hotspot-icon" src="'.$hotspot_icon.'" alt="" style="
                        left:'. (round($value['width']/2)-round($icon_dimensions['width']/2)) .'px; 
                        top:'. (round($value['height']/2)-round($icon_dimensions['height']/2)) .'px;
                        "/>';
						 //$html_content .= '<div class="hotspot-icon">'.$i.'</div>';
                     $decoded_array[$key]['icon_width'] = $icon_dimensions['width'];
                     $decoded_array[$key]['icon_height'] = $icon_dimensions['height'];
    		}
		    $html_content .=  '<div class="product-info" style="';           
                    $html_content .=  'left:'.round($value['width']/2).'px;';
                    $html_content .=  'top:'.round($value['height']/2).'px;';
                   
                if ($product_details) {
                    $_p_name = $product_details->getName();
                    if ($helper->canShowProductDescr()) {
    					$_p_shrt_desc = Mage::helper('core/string')->truncate($product_details_full->getShortDescription());
    					$_p_shrt_image = Mage::helper('catalog/image')->init($product_details_full, 'image')->resize(50,50);                        
                    }

                    $html_content .=  'width: '. strlen($_p_name)*8 .'px;';
                }
                else
                {
                    $html_content .=  'width: '. strlen($decoded_array[$key]['text'])*8 .'px;';
                }
                    $html_content .=  '"><div class="pro-detail-div">';
                
                    
                if ($product_details) {                    
        			$_p_price = $_coreHelper->currency($product_details->getFinalPrice(),true,false);
                    /** check if product is in stock */
                    /** $stockItem = $product_details->getStockItem();
                        if($stockItem->getIsInStock())
                     */
					 $html_content .=  '<div class="left-detail">';
                    if($product_details->isAvailable())
                    {
                        if ($helper->getUseFullProdUrl()) {
                            $_p_url = $helper->getFullProductUrl($product_details);
                        }
                        else {
                            $_p_url = $product_details->getProductUrl();                     
                        }           
                        
            			$html_content .= '<h2><a href=\''.$_p_url.'\' target="_blank">'.$_p_name.'</a></h2>';
                    }
                    else
                    {
                        $html_content .= '<h2>'.$_p_name.'</h2>';
                        $html_content .= '<div class="out-of-stock"><span>'. $helper->__('Out of stock') .'</span></div>';                        
                    }
                    if ($helper->canShowProductDescr()) {
					   $html_content .= '<div class="desc"><img src="'.$_p_shrt_image.'" alt="product image"/>'.$_p_shrt_desc.'</div>';
					}
                    
                    if($product_details->getFinalPrice()){
                            if ($product_details->getPrice()>$product_details->getFinalPrice()){
                                    $regular_price = $_coreHelper->currency($product_details->getPrice(),true,false);
                                    $_p_price = '<div class="old-price">'.$regular_price.'</div>'.$_p_price;
                            }
            				$html_content .= '<div class="price">'.$_p_price.'</div>';
            		}
                    if ($helper->canShowAddToCart()) {
					   $html_content .= $this->getAddToCartHtml($product_details_full);
					}
					$html_content .= '</div>';
                                       
                }
                else
                {
                    //$html_content .= '<div>Product with SKU "'.$decoded_array[$key]['text'].'" doesn\'t exists.</div>';
                    $html_content .= '<div><a href=\''.$decoded_array[$key]['href'].'\'>'.$decoded_array[$key]['text'].'</a></div>';
                }
			$html_content .= '</div></div>';
			
			$decoded_array[$key]['text'] = $html_content;
		}
        $result = $decoded_array;
        return $result;
    }

    public function getAddToCartHtml($product){
        $html = '';
        if ($product && $product->isSaleable()) {
             $block = $this->getLayout()->createBlock('lookbookslider/content_addtocart', 'lookbook_addtocart_'.$product->getId())
                                        ->setTemplate('lookbookslider/content/addtocart.phtml')
                                        ->setProduct($product);
             $html .= $block->toHtml();          
        }
        
        return $html;
    }
      
}