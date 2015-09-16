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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Directive_Static extends Varien_Object
{

    public function toHtml()
    {
        return '<div style="float:left;">'. Mage::helper('googlebasefeedgenerator')->__('Value:'). '</div>
                <div style="float:right;"><input type="text" name="groups[#{group_name}][fields][#{field_name}][value][#{_id}][param]" value="#{param}" class="input-text validate-not-empty" style="width: 180px;"></div>';
    }
}