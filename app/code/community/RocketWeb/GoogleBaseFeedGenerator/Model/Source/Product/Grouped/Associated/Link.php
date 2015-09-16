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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Grouped_Associated_Link extends RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated
{
    public function toOptionArray()
    {
        $vals = array(
            self::FROM_PARENT => Mage::helper('googlebasefeedgenerator')->__('Parent only'),
            self::FROM_ASSOCIATED_PARENT => Mage::helper('googlebasefeedgenerator')->__('Associated if is visible in catalog, otherwise from parent'),
        );
        $options = array();
        foreach ($vals as $k => $v) {
            $options[] = array('value' => $k, 'label' => $v);
        }

        return $options;
    }
}