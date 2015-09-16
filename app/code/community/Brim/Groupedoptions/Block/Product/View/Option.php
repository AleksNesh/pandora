<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file license.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_Groupedoptions
 * @copyright  Copyright (c) 2011-2012 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */
class Brim_Groupedoptions_Block_Product_View_Option extends Mage_Catalog_Block_Product_View_Options_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('grouped-options/renderer/default-option.phtml');
        }
    }

    protected function _toHtml() {
        if (($option = $this->getOption()) && ($product = $this->getProduct())
            && !$option->getProduct() ) {
            $option->setProduct($product);
        }

        return parent::_toHtml();
    }

    protected function _beforeToHtml() {
        if (($option = $this->getOption()) != null  && ($product = $this->getProduct()) != null) {
            $option->setProduct($product);
        }
        return parent::_beforeToHtml();
    }

    public function getNamePrefix() {
        if(!($namePrefix = $this->getData('name_prefix'))) {
            $namePrefix = 'super_options';
        }
        return $namePrefix;
    }
}