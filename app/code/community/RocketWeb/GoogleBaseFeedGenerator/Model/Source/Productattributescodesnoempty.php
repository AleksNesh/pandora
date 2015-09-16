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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Productattributescodesnoempty extends RocketWeb_GoogleBaseFeedGenerator_Model_Source_Productattributescodes
{

    public function toOptionArray()
    {
        if (is_null(self::$attributes)) {
            $this->loadAttributes();
        }

        $options = parent::toOptionArray();
        array_shift($options);

        return $options;
    }
}