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
class Brim_Groupedoptions_Block_Product_View_Options extends Mage_Core_Block_Template
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('grouped-options/options.phtml');
        }
    }

    protected function _toHtml() {
        $layout = $this->getLayout();
        $product = $this->getProduct();

        if ($this->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $block = $layout->createBlock('catalog/product_view_type_configurable', 'go_configurable_block_' . $product->getId(), array(
                'template'  => $this->getConfigurableTemplate(),
                'product'   => $this->getProduct()
            ));
        } else {
            $block = $layout->createBlock('groupedoptions/product_view_type_simple', 'go_simple_block_'. $product->getId(), array(
                'template'  => $this->getSimpleTemplate(),
                'product'   => $this->getProduct()
            ));
        }


        $this->setRenderedProductHtml($block->toHtml());

        return parent::_toHtml();
    }
}