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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated extends Varien_Object
{
    const FROM_PARENT = 0;
    const FROM_ASSOCIATED = 1;
    const FROM_ASSOCIATED_PARENT = 2;
    const FROM_PARENT_ASSOCIATED = 3;

    public function toOptionArray()
    {
        $vals = array(
            self::FROM_PARENT => Mage::helper('googlebasefeedgenerator')->__('Parent only'),
            self::FROM_ASSOCIATED => Mage::helper('googlebasefeedgenerator')->__('Associated only'),
            self::FROM_ASSOCIATED_PARENT => Mage::helper('googlebasefeedgenerator')->__('Associated if exists, otherwise from parent'),
            self::FROM_PARENT_ASSOCIATED => Mage::helper('googlebasefeedgenerator')->__('Parent if exists, otherwise from associated'),
        );
        $options = array();
        foreach ($vals as $k => $v) {
            $options[] = array('value' => $k, 'label' => $v);
        }

        return $options;
    }
}