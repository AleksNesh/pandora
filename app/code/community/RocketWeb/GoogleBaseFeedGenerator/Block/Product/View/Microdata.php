<?php

/**
 * Class RocketWeb_GoogleBaseFeedGenerator_Block_Product_View_Microdata
 *
 * Microdata block - supports all default product types. Configurables will include information about children
 * Uses the Feed Generator to generate map for the current product
 *  - does not create/verify locks for the generator
 *  - the map will only include required columns, hard-coded in a property
 *
 * @usage $list = $block->setProduct($product)->getMicrodata();
 *        $microdata = $list[0];
 *        $microdata->getName();
 *        $microdata->getPrice();
 *        $microdata->getCurrency();
 *        $microdata->getAvailability();
 *
 * @see RocketWeb_GoogleBaseFeedGenerator_Model_Generator
 */
class RocketWeb_GoogleBaseFeedGenerator_Block_Product_View_Microdata
    extends Mage_Catalog_Block_Product_View_Abstract
{

    const XML_PATH_ENABLED = 'rocketweb_googlebasefeedgenerator/file/microdata_turned_on';

    /** @var array */
    protected $_maps = array();

    /** @var array columns to generate by the map generator */
    protected $_columns = array('id', 'price', 'sale_price', 'availability', 'title');

    /**
     * @return bool
     */
    public function isEnabled() {
        return (Mage::getStoreConfig(self::XML_PATH_ENABLED) == "1");
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Object[]
     */
    public function getMicrodata() {
        /** @var Varien_Object[] $microdata_list */
        $microdata_list = array();

        $product = $this->getProduct();

        if ($this->isEnabled() && $product && $product->getId()) {
            try {
                foreach ($this->_getMaps() as $map) {
                    $object = $this->_createRowObject($map);
                    if ($object) {
                        $microdata_list[] = $object;
                    }
                }
            }
            catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $microdata_list;
    }

    /**
     * Converts map array to microdata Object
     *
     * @param array $map map array returned by the generator
     * @return null|Varien_Object
     */
    protected function _createRowObject($map) {
        if (empty($map['price']) || empty($map['availability']) || empty($map['title'])) {
            return null;
        }

        $microdata = new Varien_Object();
        $microdata->setName($map['title']);
        $microdata->setId($map['id']);
        if (!empty($map['sale_price'])) {
            $price = $map['sale_price'];
        }
        else {
            $price = $map['price'];
        }
        $microdata->setPrice(Zend_Locale_Format::toNumber($price, array(
            'precision' => 2,
            'number_format' => '#0.00'
        )));

        $microdata->setCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());
        if ($map['availability'] == 'in stock') {
            $microdata->setAvailability('http://schema.org/InStock');
        }
        else {
            $microdata->setAvailability('http://schema.org/OutOfStock');
        }

        if (array_key_exists('condition', $map)) {
            if (strcasecmp('new', $map['condition']) == 0) {
                $microdata->setCondition('http://schema.org/NewCondition');
            }
            else if (strcasecmp('used', $map['condition']) == 0) {
                $microdata->setCondition('http://schema.org/UsedCondition');
            }
            else if (strcasecmp('refurbished', $map['condition']) == 0) {
                $microdata->setCondition('http://schema.org/RefurbishedCondition');
            }
        }

        return $microdata;
    }

    /**
     * Set a new product and reset maps to force re-generation
     *
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function setProduct(Mage_Catalog_Model_Product $product) {
        $this->setData('product', $product);
        unset($this->_maps);
        return $this;
    }

    /**
     * Retrieves all maps for current product - will include children maps if any
     *
     * @return array
     */
    protected function _getMaps() {
        /** @var RocketWeb_GoogleBaseFeedGenerator_Model_Generator $generator */
        $generator = Mage::getSingleton('googlebasefeedgenerator/tools')
            ->setSkipLocks(true)
            ->addData(array('store_code' => Mage::app()->getStore()->getCode()))
            ->getGenerator(Mage::app()->getStore()->getId());
        $generator->setOutputColumns($this->_columns);

        if (!isset($this->_maps)) {
            $this->_maps = array();
            $clone = clone $this->getProduct();
            $this->_maps = $generator->generateProductMap($clone);
            unset($clone);
        }

        return $this->_maps;
    }

}
