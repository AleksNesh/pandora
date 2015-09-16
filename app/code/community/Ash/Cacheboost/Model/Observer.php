<?php
/**
 * Boost cachability by enabling block-level cache on strategic core Magneto
 * blocks
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @copyright   Copyright (c) 2015 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Observer model
 *
 * @category    Ash
 * @package     Ash_Cacheboost
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Cacheboost_Model_Observer
{
    /**
     * Block types to cache
     *
     * @var array
     */
    protected $_cachableBlocks   = array(
        'Mage_Cms_Block_Block'                             => 'type_cms_block',
        'Mage_Cms_Block_Page'                              => 'type_cms_page',
        'Mage_Page_Block_Html_Footer'                      => 'type_page_html_footer',
        // 'Mage_Page_Block_Html_Topmenu'                     => 'type_page_html_topmenu', // because of dynamic highlights
        'Mage_Catalog_Block_Product_Abstract'              => 'type_catalog_product_abstract',
        'Mage_Catalog_Block_Product_View'                  => 'type_catalog_product_view',
        'Mage_Catalog_Block_Product_Price'                 => 'type_catalog_product_price',
        'Mage_Catalog_Block_Product_List'                  => 'type_catalog_product_list',
        'Mage_Catalog_Block_Category_View'                 => 'type_catalog_category_view',
        'Mage_Catalog_Block_Layer_View'                    => 'type_catalog_layer_view',
        'Mage_Review_Block_Product_View_List'              => 'type_review_product_view_list',
        'Mage_Review_Block_Product_View'                   => 'type_review_product_view',
        'Enterprise_TargetRule_Block_Catalog_Product_Item' => 'type_targetrule_catalog_product_item',
    );

    /**
     * Inject cache components into block instances to enable Magento Block cache
     *
     * @param   Varien_Event_Observer $observer
     * @return  void
     */
    public function addBlockCache(Varien_Event_Observer $observer)
    {
        // log any exceptions but otherwise silently fail
        try {
            // filter out known scenarios to not add cache components
            if (Mage::app()->getRequest()->getActionName() == 'add') {
                return $this;
            }

            $block = $observer->getEvent()->getBlock();
            $class = get_class($block);

            // if the block is of the correct type, add appropriate cache components
            foreach ($this->_cachableBlocks as $_blockClass => $_modelCode) {
                /*
                 * ALSO CHECK IF TYPE IS ENABLED?
                 */
                if ($block instanceof $_blockClass) {
                    // instantiate proper model
                    $model = Mage::getModel('ash_cacheboost/' . $_modelCode);
                    $model->setBlock($block);

                    // inject cache components into block instance
                    $block
                        ->setData('cache_lifetime', $model->getCacheLifetime())
                        ->setData('cache_key', $model->getCacheKey())
                        ->setData('cache_tags', $model->getCacheTags());
                }
            }

            return $this;
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
