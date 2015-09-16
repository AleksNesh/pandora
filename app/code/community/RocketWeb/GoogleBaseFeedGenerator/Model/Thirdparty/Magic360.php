<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_GoogleBaseFeedGenerator
 * @copyright Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */
class RocketWeb_GoogleBaseFeedGenerator_Model_Thirdparty_Magic360 extends Varien_Object
{
    public function _construct()
    {
        $this->setData(array(
            'helper' => Mage::helper('magic360/settings'),
            'tool'  => Mage::helper('magic360/settings')->loadTool(),
            'basedir' => Mage::getBaseDir('media') . DS . '360images'
        ));
        parent::_construct();
    }

    private function _getRawImages($product)
    {
        $images = array();
        $imagesCount = 5; /* max is 18 images */

        /* build the file name  */
        $_img_name = substr($product->getSku(), 0, -6);
        $_img_name  = Mage::getModel('catalog/product_url')->formatUrlKey($_img_name);

        /* check if file exists */
        if(file_exists($this->getBasedir() . DS . $_img_name . "_0001.jpg" )){
            for( $i = 1; $i <= $imagesCount ; $i++){
                $images[] = array(
                    'file' =>   $_img_name .  "_" . sprintf('%04d', $i)  . ".jpg"
                );
            }
        }
        return $images;
    }

    private function _getCacheImage($image)
    {
        Mage::helper('magic360/image')->init($image)->setBaseDir($this->getBasedir())->__toString();
        $img =  Mage::getBaseUrl('media', false) . "360images/" . $image;

        $originalSizeArray = Mage::helper('magic360/image')->getOriginalSizeArray();
        if($this->getTool()->params->checkValue('square-images', 'Yes')) {
            $big_image_size = ($originalSizeArray[0] > $originalSizeArray[1]) ? $originalSizeArray[0] : $originalSizeArray[1];
            $img = Mage::helper('magic360/image')->setWatermarkFile(null)->resize($big_image_size)->__toString();
        }
        list($w, $h) = $this->getHelper()->magicToolboxGetSizes('thumb', $originalSizeArray);

        $medium = Mage::helper('magic360/image')->setWatermarkFile(null)->resize($w, $h)->__toString();
        $medium = str_replace('https://', 'http://', $medium);

        return compact('img', 'medium');
    }

    public function getProductImages($product)
    {
        if (method_exists($product->getTypeInstance(), 'getUsedProducts')) {
            return $this->getComplexProductImages($product->getTypeInstance()->getUsedProducts());
        }
        return $this->getSimpleProductImages($product);
    }

    protected function getSimpleProductImages($product)
    {
        $images = array();
        $imageSet = $this->_getRawImages($product);
        foreach ($imageSet as $img) {
            if(file_exists($this->getBasedir(). DS. $img['file'])){
                $images[] = $this->_getCacheImage($img['file']);
            }
        }
        return $images;
    }

    protected function getComplexProductImages($products)
    {
        $imageSets = array();

        foreach ($products as $product) {
            $_color = $product->getColor();

            if(!isset($imageSets[$_color])){
                $images = $this->_getRawImages($product);
                if($images && count($images)) {
                    $imageSets[$_color] = $images;
                }
            }
        }

        foreach($imageSets as $magic360Images){
            if($magic360Images && count($magic360Images)) {
                $_images = array();
                foreach($magic360Images as $_image) {
                    if(!file_exists($this->getBasedir(). DS. $_image['file'])){
                        continue;
                    }
                    $_images[] = $this->_getCacheImage($_image['file']);
                }
            }
        }

        return $_images;
    }
}