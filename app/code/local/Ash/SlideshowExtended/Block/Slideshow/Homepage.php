<?php

class Ash_SlideshowExtended_Block_Slideshow_Homepage extends Ash_Slideshow_Block_Slideshow_Abstract
{
    public function _construct()
    {
        parent::_construct();

        /**
         * update the $_slideshowId value, but only if the
         * System > Configuration > Ash Slideshow (Extended)
         * is configured to be enabled and is available to be
         * shown on pages (i.e., home page)
         */
        if (Mage::helper('ash_slideshowextended')->isSlideshowEnabled()) {
            $this->setSlideshowId(Mage::helper('ash_slideshowextended')->getSlideshowId());
        }
    }
}
