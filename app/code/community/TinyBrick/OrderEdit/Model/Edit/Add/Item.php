<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Model_Edit_Add_Item 
{
        /**
         * This is for the add new item. Gives an update box if there are options
         * @param string $sku Sku of product
         * @param string $color Color of product
         * @param string $size Size of product
         * @return object
         */
	public function updateBox($sku, $color, $size)
	{
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
		$options = array();
		return $this->getOptions($product, $color, $size);
	}
	/**
         * Gets the options for the new product
         * @param string $product
         * @param string $color
         * @param string $size
         * @return object 
         */
	public function getOptions($product, $color = "", $size = "") 
	{
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('type_id', 'simple')
			->addAttributeToFilter('sku', array('like'=>$product->getSku().'%'))
			->addAttributeToSelect('size')
			->addAttributeToSelect('color');
		$collection->getSelect()
			->join(array('si' => 'cataloginventory_stock_item'), 'e.entity_id = si.product_id AND si.qty > 0')
			->columns('qty', 'si');
		
		$array = array();
		foreach($collection as $simple) {
			$array[] = array(
				'simple_sku'    => $simple->getSku(),
				'color_id' 		=> intval($simple->getData('color')), 
				'color_value' 	=> $simple->getAttributeText('color'),
				'size_id' 		=> intval($simple->getData('size')),
				'size_value' 	=> $simple->getAttributeText('size'),
				'qty' 			=> $simple->getQty(),
			);
		}
		$arrReturn = array();
		
		$arrReturn['colors'] = $this->buildColor($array);

		if($color == "") {
			$color = $array[0]['color_id'];
		}
		$arrReturn['sizes'] = $this->buildSize($array, $color);
		
		if($size == "") {
			$size = $array[0]['size_id'];
		}
		$arrReturn['qtys'] = $this->buildQty($array, $color, $size);
		
		/**
                 * create json to return
                 */
		return Zend_Json::encode($arrReturn);
	}
	/** 
         * Builds the color of the object - Drop down box
         * @param array $arr Array of colors
         * @return string
         */
	public function buildColor($arr)
	{
		//color dropdown builder
		$colorArray = array();
		foreach($arr as $color) {
			$colorArray[$color['color_id']] = $color['color_value'];
		}

		$color = "<select name='items[color]' id='color-value'>";
		foreach($colorArray as $key => $option) {
			$color .= "<option value='" . $key . "'>" . $option . "</option>";
		}
		$color .= "</select>";
		return $color;
	}
	/**
         * Builds the size of the new product
         * @param array $arr Array of sizes
         * @param string $productColor Color of the product
         * @return string 
         */
	public function buildSize($arr, $productColor)
	{
		$size = "<select name='items[size]' id='size-value'>";
		foreach($arr as $option) {
			if($option['color_id'] == $productColor) {
				$size .= "<option value='" . $option['size_id'] . "-" . $option['simple_sku'] . "'>" . $option['size_value'] . "</option>";
			}
		}
		$size .= "</select>";
		return $size;
	}
	/**
         * Builds the qty of how many products you want
         * @param array $arr Array of options
         * @param string $productColor Color of product
         * @param string $productSize Size of product
         * @return string 
         */
	public function buildQty($arr, $productColor, $productSize)
	{
		$qty = "<select name='items[qty]' id='qty-value'>";
		foreach($arr as $option) {
			if($option['color_id'] == $productColor && $option['size_id']) {
				$maxqty = $option['qty'];
			}
		}
		$x = 1;
		while($x <= $maxqty) {
			$qty .= "<option value='" . $x . "'>" . $x++ . "</option>";
		}
		$qty .= "</select>";
		return $qty;
	}
}