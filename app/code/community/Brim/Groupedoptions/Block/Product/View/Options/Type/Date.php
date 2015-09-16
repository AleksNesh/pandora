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

class Brim_Groupedoptions_Block_Product_View_Options_Type_Date
    extends Mage_Catalog_Block_Product_View_Options_Type_Date {

    public function getCalendarDateHtml()
    {
        $product    = $this->getProduct();
        $option     = $this->getOption();
        $value      = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/date');

        //$require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';

        $class = ' product-custom-option-' . $product->getId();

        $yearStart = Mage::getSingleton('catalog/product_option_type_date')->getYearStart();
        $yearEnd = Mage::getSingleton('catalog/product_option_type_date')->getYearEnd();

        $calendar = $this->getLayout()
            ->createBlock('core/html_date')
            ->setId('superoptions_'.$this->getOption()->getId().'_date')
            ->setName($this->getNamePrefix() . '['. $product->getId() .']['.$this->getOption()->getId().'][date]')
            ->setClass('product-custom-option datetime-picker input-text' . $require . $class)
            ->setImage($this->getSkinUrl('images/calendar.gif'))
            ->setFormat(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT))
            ->setValue($value)
            ->setYearsRange('[' . $yearStart . ', ' . $yearEnd . ']');
        if (!$this->getSkipJsReloadPrice()) {
            $calendar->setExtraParams('onchange="optionsConfig' . $product->getId() . '.reloadPrice()"');
        }

        return $calendar->getHtml();
    }

    protected function _getHtmlSelect($name, $value = null)
    {
        $product    = $this->getProduct();
        $option     = $this->getOption();

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $class = ' product-custom-option-' . $product->getId();

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setId('superoptions_' . $this->getOption()->getId() . '_' . $name)
            ->setClass('product-custom-option datetime-picker' . $require . $class)
            ->setExtraParams()
            ->setName($this->getNamePrefix() . '['. $product->getId() .'][' . $option->getId() . '][' . $name . ']');

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="optionsConfig' . $product->getId() . '.reloadPrice()"';
        }
        $select->setExtraParams($extraParams);

        if (is_null($value)) {
            $value = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/' . $name);
        }
        if (!is_null($value)) {
            $select->setValue($value);
        }

        return $select;
    }
}