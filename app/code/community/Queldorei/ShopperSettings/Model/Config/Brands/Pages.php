<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Model_Config_Brands_Pages
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'1',
	            'label' => Mage::helper('shoppersettings')->__('Home page only')),
	        array(
	            'value'=>'2',
	            'label' => Mage::helper('shoppersettings')->__('All pages')),
        );
    }

}
