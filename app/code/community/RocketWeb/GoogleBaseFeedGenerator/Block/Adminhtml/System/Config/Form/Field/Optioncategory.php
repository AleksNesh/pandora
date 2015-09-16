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

class RocketWeb_GoogleBaseFeedGenerator_Block_Adminhtml_System_Config_Form_Field_Optioncategory
    extends RocketWeb_GoogleBaseFeedGenerator_Block_Adminhtml_System_Config_Form_Field_Categorytree
{
    public function getLabel() {
        return $this->__('All categories');
    }

    public function getNote() {
        return '<p class="note">'. $this->__('If specified categories here, products outside the selection will be a single row in the feed having the option values comma separated in the column.'). '</p>';
    }

    public function getJsFormObject()
    {
        return 'categories_vary_form';
    }
}