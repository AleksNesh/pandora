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
class RocketWeb_GoogleBaseFeedGenerator_Model_Source_Directive_Product_Url extends Varien_Object
{

    public function toHtml()
    {
        return '<div style="float:left;">'. Mage::helper('googlebasefeedgenerator')->__('Add URL Suffix:'). '</div>
                <div style="float:right;"><input type="text" name="groups[#{group_name}][fields][#{field_name}][value][#{_id}][param]" value="#{param}" class="input-text" style="width:180px;"></div>
                <p class="note" style="clear:both;"><span>' . Mage::helper('googlebasefeedgenerator')->__('Common usage is to send extra GET parameters for analytics tracking.'). '</span></p>';
    }
}