<?php

/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 *
 */
class Ash_Slideshow_Block_Widget extends Ash_Slideshow_Block_Slideshow_Abstract implements Mage_Widget_Block_Interface
{
    protected function _beforeToHtml()
    {
        $this->setSlideshowId($this->getData('ash_slideshow_id'));
        parent::_beforeToHtml();
    }
}
