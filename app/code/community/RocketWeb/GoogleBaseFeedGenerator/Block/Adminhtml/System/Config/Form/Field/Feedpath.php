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
 * @copyright Copyright (c) 2015 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Block_Adminhtml_System_Config_Form_Field_Feedpath
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _prepareLayout()
    {
        $this->setTemplate('googlebasefeedgenerator/system/config/form/field/feedpath.phtml');
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->_toHtml();
    }

    public function getFeedUrl()
    {
        $config = $this->getConfigData();
        $key = RocketWeb_GoogleBaseFeedGenerator_Model_Config::XML_PATH_RGBF . '/file/feed_filename';
        $feed_file = array_key_exists($key, $config) ? $config[$key] : 'google_base_%s.txt';
        $store = Mage::app()->getRequest()->getParam('store', Mage_Core_Model_Store::DEFAULT_CODE);
        $path = DS . rtrim($this->getElement()->getValue(), DS) . DS . sprintf($feed_file, $store);

        return 'URL: <a href="' . rtrim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), DS) . $path . '" target="blank">' . $path . '</a>';
    }

}