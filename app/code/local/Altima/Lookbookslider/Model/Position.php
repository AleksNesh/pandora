<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Model_Position extends Mage_Core_Model_Abstract
{
    const CONTENT_TOP       = 'CONTENT_TOP';
    const CONTENT_BOTTOM    = 'CONTENT_BOTTOM';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::CONTENT_TOP, 'label'=>Mage::helper('lookbookslider')->__('Content Top')),
            array('value' => self::CONTENT_BOTTOM, 'label'=>Mage::helper('lookbookslider')->__('Content Bottom'))
        );
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toGridOptionArray()
    {
        return array(
            self::CONTENT_TOP => Mage::helper('lookbookslider')->__('Content Top'),
            self::CONTENT_BOTTOM => Mage::helper('lookbookslider')->__('Content Bottom')
        );
    }
}
