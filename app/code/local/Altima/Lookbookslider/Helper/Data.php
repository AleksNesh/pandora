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
class Altima_Lookbookslider_Helper_Data extends Mage_Core_Helper_Abstract
{
    function __construct()
    {
        $this->temp = Mage::getStoreConfig('lookbookslider/general/' . base64_decode('c2VyaWFs'));
    }
    
    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array())
    {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }

   public function getEnabled()
	{
		return Mage::getStoreConfig('lookbookslider/general/enabled');
	}

   public function getEnableJquery()
	{
		return Mage::getStoreConfig('lookbookslider/general/enable_jquery');
	}
    
   public function getUseFullProdUrl()
	{
		return Mage::getStoreConfig('lookbookslider/general/cat_path_in_prod_url');
	}
        
   public function getInterdictOverlap()
	{
       $value = Mage::getStoreConfig('lookbookslider/general/interdict_areas_overlap');
	   if ($value==1) {
	       return 'true'; 
	   }
       else {
            return 'false';
       } 
	}
     
    public function getMaxUploadFilesize()
	{
		return intval(Mage::getStoreConfig('lookbookslider/general/max_upload_filesize'));
	}
  
    public function getAllowedExtensions()
	{
		return Mage::getStoreConfig('lookbookslider/general/allowed_extensions');
	} 

    public function canShowProductDescr()
	{
		return Mage::getStoreConfig('lookbookslider/general/show_product_desc');
	} 
    
    public function canShowAddToCart()
	{
		return Mage::getStoreConfig('lookbookslider/general/show_add_to_cart');
	} 
        
    public function getHotspotIcon()
	{
	    $config_icon_path = Mage::getStoreConfig('lookbookslider/general/hotspot_icon');
        if ($config_icon_path=='') $config_icon_path = 'default/hotspot-icon.png';
		return Mage::getBaseUrl('media').'lookbookslider/icons/'.$config_icon_path;
	}
    
    public function getHotspotIconPath()
	{
        $config_icon_path = Mage::getStoreConfig('lookbookslider/general/hotspot_icon');
        if ($config_icon_path=='') $config_icon_path = 'default/hotspot-icon.png';
		return Mage::getBaseDir('media').DS.'lookbookslider'.DS.'icons'.DS.str_replace('/', DS, $config_icon_path);
	}
       
	/**
	* Returns the resized Image URL
	*
	* @param string $imgUrl - This is relative to the the media folder (custom/module/images/example.jpg)
	* @param int $x Width
	* @param int $y Height
	*Remember your base image or big image must be in Root/media/lookbookslider/example.jpg
	*
	* echo Mage::helper('lookbookslider')->getResizedUrl("lookbookslider/example.jpg",101,65)
	*
	*By doing this new image will be created in Root/media/lookbookslider/101X65/example.jpg
	*/

    public function getResizedUrl($imgUrl,$x,$y=NULL){

        $imgPath=$this->splitImageValue($imgUrl,"path");
        $imgName=$this->splitImageValue($imgUrl,"name");
 
        /**
         * Path with Directory Seperator
         */
        $imgPath=str_replace("/",DS,$imgPath);
 
        /**
         * Absolute full path of Image
         */
        $imgPathFull=Mage::getBaseDir("media").DS.$imgPath.DS.$imgName;
        
        /**
         * If Y is not set set it to as X
         */
        $width=$x;
        $y?$height=$y:$height=$x;
 
        /**
         * Resize folder is widthXheight
         */
        $resizeFolder=$width."X".$height;
 
        /**
         * Image resized path will then be
         */
        $imageResizedPath=Mage::getBaseDir("media").DS.$imgPath.DS.$resizeFolder.DS.$imgName;
        
        /**
         * First check in cache i.e image resized path
         * If not in cache then create image of the width=X and height = Y
         */
        if (!file_exists($imageResizedPath) && file_exists($imgPathFull)) :
            $imageObj = new Varien_Image($imgPathFull);
            $imageObj->constrainOnly(FALSE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepTransparency(TRUE);
            $imageObj->keepFrame(FALSE);
        //    $imageObj->resize($width,$height);
            
           // $widthDistance = $imageObj->getOriginalWidth() - $width;
           // $heightDistance = $imageObj->getOriginalHeight() - $height;
            
          
            if (($width / $height) > ($imageObj->getOriginalWidth() / $imageObj->getOriginalHeight()))
            {
                  $imageObj->resize($width, null);
            }else{
                  $imageObj->resize(null, $height);
            }            
            $cropX = 0;
            $cropY = 0;
  if ($imageObj->getOriginalWidth() > $width)
  {
    $cropX = intval(($imageObj->getOriginalWidth() - $width) / 2);
  }
  elseif ($imageObj->getOriginalHeight() > $height)
  {
    $cropY = intval(($imageObj->getOriginalHeight() - $height) / 2);
  }
            
            $imageObj->crop($cropY,$cropX,$cropX,$cropY);
          
            $imageObj->save($imageResizedPath);
        endif;
 
        /**
         * Else image is in cache replace the Image Path with / for http path.
         */
        $imgUrl=str_replace(DS,"/",$imgPath);
 
        /**
         * Return full http path of the image
         */
        return Mage::getBaseUrl("media").$imgUrl."/".$resizeFolder."/".$imgName;
    }
 
    /**
     * Splits images Path and Name
     *
     * Path=lookbook/
     * Name=example.jpg
     *
     * @param string $imageValue
     * @param string $attr
     * @return string
     */
    public function splitImageValue($imageValue,$attr="name"){
        $imArray=explode("/",$imageValue);
 
        $name=$imArray[count($imArray)-1];
        $path=implode("/",array_diff($imArray,array($name)));
        if($attr=="path"){
            return $path;
        }
        else
            return $name;
 
    }
    
     /**
     * Splits images Path and Name
     *
     * img_path=lookbook/example.jpg
     *
     * @param string $img_path
     * @return array('width'=>$width, 'height'=>$height)
     */ 
    public function getImageDimensions($img_path){
        if (file_exists($img_path)) {
            $imageObj = new Varien_Image($img_path);
            $width = $imageObj->getOriginalWidth();
            $height = $imageObj->getOriginalHeight();
            $result = array('width'=>$width, 'height'=>$height);
        }
        else
        {
            $result = array('error'=>"$img_path does not exists");
        }
        return $result;
    }
    
        
    public function getFullProductUrl($product) {
        if (is_object($product) && $product->getSku()) {
            $allCategoryIds = $product->getCategoryIds();
            $lastCategoryId = end($allCategoryIds);
            $lastCategory = Mage::getModel('catalog/category')->load($lastCategoryId);
            $lastCategoryUrl = $lastCategory->getUrl();
            $url = str_replace(Mage::getStoreConfig('catalog/seo/category_url_suffix'), '/', $lastCategoryUrl) . basename($product->getUrlKey()) . Mage::getStoreConfig('catalog/seo/product_url_suffix');
        } else {
            $url = '';
        }
        return $url;
    }

    function checkEntry($domain, $ser)
    {
        if ($this->isEnterpr()) {
           $key = sha1(base64_decode('bG9va2Jvb2tzbGlkZXJfZW50ZXJwcmlzZQ=='));
        }
        else
        {
           $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXI=')); 
        }

	$domain = str_replace('www.','',$domain);
	$www_domain = 'www.'.$domain;

        if(sha1($key.$domain) == $ser || sha1($key.$www_domain) == $ser)   {
            return true;
        }

        return false;
    }

    function checkEntryDev($domain, $ser)
    {
        $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXJfZGV2'));
	
	$domain = str_replace('www.','',$domain);	
	$www_domain = 'www.'.$domain;
        if(sha1($key.$domain) == $ser || sha1($key.$www_domain) == $ser)   {
            return true;
        }
        
        return false;

    }

    public function canRun($dev=false)
    {

        $temp = trim($this->temp);
	$m = $temp[0];
	$temp=substr($temp, 1);
        if ($m) {
           $base_url = parse_url(Mage::getStoreConfig('web/unsecure/base_url',0));
           $base_url = $base_url['host'];
        }
        else
        {
           $base_url = $_SERVER['SERVER_NAME'];
        }


        if(!$dev) {
            $original = $this->checkEntry($base_url, $temp);
        } else {
            $original = $this->checkEntryDev($base_url, $temp);
        }

        if(!$original) {
           return false;
        }
	
        return true;
    } 
    
    function isEnterpr()
    {      
	return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') && Mage::getConfig()->getModuleConfig('Enterprise_Checkout') && Mage::getConfig()->getModuleConfig('Enterprise_Customer');     
    } 
      
}
