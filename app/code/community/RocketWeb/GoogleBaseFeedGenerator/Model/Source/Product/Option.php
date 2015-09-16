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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Option extends Varien_Object
{

    const ONE_ROW = 0;
    const MULTIPLE_ROWS = 1;

    public function toOptionArray()
    {
        $vals = array(
            self::ONE_ROW         => Mage::helper('googlebasefeedgenerator')->__('One row, having options concatenated in the column output'),
            self::MULTIPLE_ROWS   => Mage::helper('googlebasefeedgenerator')->__('Multiple rows, one for each option'),
        );

        $options = array();
        foreach ($vals as $k => $v) {
            $options[] = array('value' => $k, 'label' => $v);
        }

        return $options;
    }
}