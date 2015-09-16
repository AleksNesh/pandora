<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  RocketWeb
 * @package   RocketWeb_GoogleBaseFeedGenerator
 * @copyright Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */


/**
 * General functions
 *
 * @category RocketWeb
 * @package  RocketWeb_GoogleBaseFeedGenerator
 */
class RocketWeb_GoogleBaseFeedGenerator_Model_Tools extends Varien_Object
{

    /**
     * All catalog_product eav attributes used.
     * @var array of RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Attribute
     */
    protected $_attributes = array();
    protected $_configurable_attributes_array = array();
    protected $_configurable_attributes_codes = array();

    public function _construct()
    {
        parent::_construct();
        $this->loadEntityType('catalog_product');

        $attr_status = $this->getAttribute('status');
        $this->setData('product_status_attribute_id', $attr_status->getAttributeId());
        unset($attr_status);
    }

    public function loadEntityType($type)
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                if (is_string($t)) {
                    $this->loadEntityType($t);
                }
            }
        } else {
            $entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');

            Mage::register('googlebasefeedgenerator/entity_type/' . $type, $entityType);
        }
        return $this;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getEntityType($type)
    {
        return Mage::registry('googlebasefeedgenerator/entity_type/' . $type);
    }

    /**
     * @param $attribute
     * @param string $type
     * @param $productId
     * @param null $storeId
     * @param bool $strict
     * @param bool $debug
     * @return array|null
     */
    public function getProductAttributeValueBySql($attribute, $type = "text", $productId, $storeId = null, $strict = false, $debug = false)
    {
        if (array_search($type, array('text', 'int', 'decimal', 'varchar', 'datetime')) === false) {
            Mage::throwException(sprintf("Unknown attribute backend type %s for attribute code %s.", $type, $attribute->getAttributeCode()));
        }

        if (is_null($storeId)) {
            return $this->getProductAttributeValueBySql($attribute, $type, $productId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
        }

        $attributeId = $attribute->getAttributeId();

        $sql = "SELECT val.value
			FROM " . $this->getRes()->getTableName('catalog/product') . "_" . $type . " val
			INNER JOIN " . $this->getRes()->getTableName('eav/attribute') . " eav ON val.attribute_id=eav.attribute_id
			WHERE
				val.entity_id='" . addslashes($productId) . "'
				AND
				val.entity_type_id = " . $this->getEntityType('catalog_product')->getEntityTypeId() . "
				AND
				val.store_id = '" . addslashes($storeId) . "'
				AND
				val.attribute_id = '" . addslashes($attributeId) . "'";
        if ($debug) {
            var_dump($sql);
        }
        $value = $this->getConnRead()->fetchCol($sql);
        if (is_array($value) && @$value[0] === null) {
            $value = null;
        } elseif (is_array($value) && isset($value[0]))
            $value = $value[0];
        else if (is_array($value) && count($value) == 0) {
            $value = null;
        }

        if (is_null($value) && $storeId != Mage_Core_Model_App::ADMIN_STORE_ID && $strict === false) {
            return $this->getProductAttributeValueBySql($attribute, $type, $productId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
        }

        return $value;
    }

    /**
     * Check if $sku has a parent of specified type (configurable, grouped, ..)
     *
     * @param  string $type_id
     * @param  string $sku
     * @param  string $parent_type_id
     * @return array|false
     */
    public function isChildOfProductType($type_id, $sku, $parent_type_id, $store_id = null)
    {
        if ($type_id != Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            return false;
        }

        $sql = "SELECT
					`cpe`.`entity_id` AS 'entity_id',
					`cpe`.`sku` AS 'sku',
					`cpe_parent`.`entity_id` AS 'parent_entity_id',
					`cpe_parent`.`sku` AS 'parent_sku',
					`cpe_parent_status`.`value` AS 'parent_status',
                    `cpe_parent_status`.`store_id` AS 'parent_status_store'
				FROM `" . $this->getRes()->getTableName('catalog/product') . "` AS `cpe` ";

        if (in_array($parent_type_id, array(RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Grouped::PRODUCT_TYPE_SUBSCTIPTION_GROUPED,
                                            Mage_Catalog_Model_Product_Type::TYPE_GROUPED))) {
            $sql .= "INNER JOIN `" . $this->getRes()->getTableName('catalog/product_link') . "` AS `cpl`
                    ON `cpe`.`entity_id`=`cpl`.`linked_product_id`
                INNER JOIN `" . $this->getRes()->getTableName('catalog/product') . "` AS `cpe_parent`
					 ON `cpl`.`product_id`=`cpe_parent`.`entity_id` ";
        } else {
            $sql .= "INNER JOIN `" . $this->getRes()->getTableName('catalog/product_super_link') . "` AS `cpsl`
                    ON `cpe`.`entity_id`=`cpsl`.`product_id`
                INNER JOIN `" . $this->getRes()->getTableName('catalog/product') . "` AS `cpe_parent`
					ON `cpsl`.`parent_id`=`cpe_parent`.`entity_id` ";
        }

        $sql .= "INNER JOIN `" . $this->getRes()->getTableName('catalog/product') . "_int` AS `cpe_parent_status`
				    ON (`cpe_parent_status`.`entity_id` = `cpe_parent`.`entity_id` AND `cpe_parent_status`.`attribute_id` = '" . $this->getData('product_status_attribute_id') . "') ";


        if (!is_null($store_id)) {
            $sql .= "INNER JOIN " . $this->getRes()->getTableName('core/store') . " AS s
                        ON s.store_id = \"" . $store_id . "\"
                    INNER JOIN " . $this->getRes()->getTableName('catalog/product_website') . " AS pw1
                        ON (s.website_id = pw1.website_id AND pw1.product_id = cpe.entity_id)
                    INNER JOIN " . $this->getRes()->getTableName('catalog/product_website') . " AS pw2
                        ON (s.website_id = pw2.website_id AND pw2.product_id = cpe_parent.entity_id) ";
        }

        $sql .= "WHERE `cpe`.`sku`=\"" . addslashes($sku) . "\"";

        if (in_array($parent_type_id, array(RocketWeb_GoogleBaseFeedGenerator_Model_Map_Product_Subscription_Grouped::PRODUCT_TYPE_SUBSCTIPTION_GROUPED,
            Mage_Catalog_Model_Product_Type::TYPE_GROUPED))) {
            $sql .= "AND `cpl`.`link_type_id`=\"" . Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED . "\"";
        }

        $sql .= "AND `cpe_parent`.`type_id`=\"" . $parent_type_id . "\"
                AND (" . (!is_null($store_id) ? "(`cpe_parent_status`.`value` = 1 AND `cpe_parent_status`.`store_id` = $store_id) OR" : '')
            . "(`cpe_parent_status`.`value` = 1 AND `cpe_parent_status`.`store_id` = 0))";


        $result = $this->getConnRead()->fetchAll($sql);
        return (is_array($result) && count($result) > 0) ? $result : false;
    }

    /**
     * @param $attribute
     * @param $valueId
     * @param null $storeId
     * @param bool $strict
     * @param bool $debug
     * @return array|null|string
     */
    public function getProductAttributeSelectValue($attribute, $valueId, $storeId = null, $strict = false, $debug = false)
    {
        if (is_null($storeId)) {
            return $this->getProductAttributeSelectValue($attribute, $valueId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
        }

        $attributeId = $attribute->getAttributeId();

        $sql = "SELECT optval.value
			FROM " . $this->getRes()->getTableName('eav/attribute_option') . " opt
			INNER JOIN " . $this->getRes()->getTableName('eav/attribute_option_value') . " optval ON opt.option_id=optval.option_id
			WHERE opt.attribute_id = '" . addslashes($attributeId) . "'
			AND optval.store_id = '" . addslashes($storeId) . "'";
        if (strpos($valueId, ',') > 0) {
            $sql .= " AND opt.option_id IN (" . addslashes($valueId) . ")";
        } else {
            $sql .= " AND opt.option_id = '" . addslashes($valueId) . "'";
        }

        if ($debug) {
            var_dump($sql);
        }

        $value = $this->getConnRead()->fetchCol($sql);
        if (is_array($value) && @$value[0] === null) {
            $value = null;
        } elseif (is_array($value) && isset($value[0])) {
            $value = implode($this->getConfig()->getConfigVar('attribute_merge_value_separator', $storeId, 'configurable_products'), $value);
        } elseif (is_array($value) && count($value) == 0) {
            $value = null;
        }

        if (is_null($value) && $storeId != Mage_Core_Model_App::ADMIN_STORE_ID && $strict === false) {
            return $this->getProductAttributeSelectValue($attribute, $valueId, Mage_Core_Model_App::ADMIN_STORE_ID, true, $debug);
        }

        return $value;
    }

    /**
     * Get categories ids by product id.
     *
     * @param  string $type_id
     * @param  string $sku
     * @param  string $parent_type_id
     * @return array|false
     */
    public function getCategoriesById($productId)
    {
        $data = false;

        $sql = "SELECT
					`category_id`
				FROM `" . $this->getRes()->getTableName('catalog/category_product') . "`
				WHERE
					`product_id`=\"" . addslashes($productId) . "\"";
        $result = $this->getConnRead()->fetchAll($sql);

        if ($result !== false) {
            $data = array();
            foreach ($result as $k => $row) {
                $data[] = $row['category_id'];
            }
        }
        return $data;
    }

    /**
     * Gets stores ids of product(s).
     * @param int|array $productId
     * @return array()
     */
    public function getProductInStoresIds($productId)
    {
        if (is_array($productId)) {
            $value = array();
            foreach ($productId as $pid) {
                $value[$pid] = array();
            }
        }

        $sql = "SELECT ";
        if (is_array($productId)) {
            $sql .= " pw.product_id AS 'product_id', s.store_id AS 'store_id'";
        } else {
            $sql .= " s.store_id ";
        }
        $sql .= " FROM " . $this->getRes()->getTableName('catalog/product_website') . " AS pw
			INNER JOIN " . $this->getRes()->getTableName('core/store') . " AS s
				ON s.website_id = pw.website_id
			WHERE";
        if (is_array($productId)) {
            $sql .= " pw.product_id IN (\"" . implode("\",\"", $productId) . "\")";
            $rows = $this->getConnRead()->fetchAll($sql);
            foreach ($rows as $row) {
                if (!isset($value[$row['product_id']])) {
                    $value[$row['product_id']] = array();
                }
                $value[$row['product_id']][] = $row['store_id'];
            }
        } else {
            $sql .= " pw.product_id=\"" . addslashes($productId) . "\"";
            $value = $this->getConnRead()->fetchCol($sql);
        }

        return $value;
    }


    /**
     * @param int $productId - parent product id
     * @return array
     */
    public function getConfigurableChildsIds($productId, $store_id = null)
    {
        $data = false;
        $sql = "SELECT `cpe`.`entity_id`,
                    `cpe_status`.`value` AS 'status',
                    `cpe_status`.`store_id` AS 'status_store'
				FROM " . $this->getRes()->getTableName('catalog/product') . " AS `cpe`
				INNER JOIN " . $this->getRes()->getTableName('catalog/product_super_link') . " AS `cpsl`
					ON `cpe`.`entity_id`=`cpsl`.`product_id`
				INNER JOIN " . $this->getRes()->getTableName('catalog/product') . " AS `cpe_parent`
					ON (`cpsl`.`parent_id`=`cpe_parent`.`entity_id`)
                INNER JOIN `" . $this->getRes()->getTableName('catalog/product') . "_int` AS `cpe_status`
				    ON (`cpe_status`.`entity_id` = `cpe`.`entity_id` AND `cpe_status`.`attribute_id` = '" . $this->getData('product_status_attribute_id') . "')

				WHERE `cpe_parent`.`entity_id`=\"" . addslashes($productId) . "\"
                    AND (" . (!is_null($store_id) ? "(`cpe_status`.`value` = 1 AND `cpe_status`.`store_id` = $store_id) OR" : '')
                    . "(`cpe_status`.`value` = 1 AND `cpe_status`.`store_id` = 0))";

        $result = $this->getConnRead()->fetchAll($sql);

        if ($result !== false) {
            foreach ($result as $row) {
                $data[] = $row['entity_id'];
            }
        }

        return $data;
    }

    /**
     * @param int $productId - parent product id
     * @return array
     */
    public function getGroupedChildsIds($productId, $store_id = null)
    {
        $data = false;
        $sql = "SELECT `cpe`.`entity_id`,
                    `cpe_status`.`value` AS 'status',
                    `cpe_status`.`store_id` AS 'status_store'
				FROM " . $this->getRes()->getTableName('catalog/product') . " AS `cpe`
				INNER JOIN " . $this->getRes()->getTableName('catalog/product_link') . " AS `cpl`
					ON (`cpe`.`entity_id`=`cpl`.`linked_product_id` AND `cpl`.`link_type_id` = '" . Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED . "')
				INNER JOIN " . $this->getRes()->getTableName('catalog/product') . " AS `cpe_parent`
					ON (`cpl`.`product_id`=`cpe_parent`.`entity_id`)
				INNER JOIN `" . $this->getRes()->getTableName('catalog/product') . "_int` AS `cpe_status`
				    ON (`cpe_status`.`entity_id` = `cpe`.`entity_id` AND `cpe_status`.`attribute_id` = '" . $this->getData('product_status_attribute_id') . "')

				WHERE `cpe_parent`.`entity_id`=\"" . addslashes($productId) . "\"
                    AND (" . (!is_null($store_id) ? "(`cpe_parent_status`.`value` = 1 AND `cpe_parent_status`.`store_id` = $store_id) OR" : '')
                    . "(`cpe_status`.`value` = 1 AND `cpe_status`.`store_id` = 0))";


        $result = $this->getConnRead()->fetchAll($sql);

        if ($result !== false) {
            foreach ($result as $row) {
                $data[] = $row['entity_id'];
            }
        }

        return $data;
    }

    /**
     * @param int $productId - Parent product id
     * @return array
     */
    public function getOptionCodes($productId)
    {
        if (!array_key_exists($productId, $this->_configurable_attributes_codes)) {

            $this->_configurable_attributes_codes[$productId] = array();
            $sql = "SELECT
                        `csa`.`attribute_id`, `eav`.`attribute_code`
                    FROM " . $this->getRes()->getTableName('catalog/product_super_attribute') . " AS `csa`
                    INNER JOIN " . $this->getRes()->getTableName('eav/attribute') . " AS `eav`
                        ON `eav`.`attribute_id`=`csa`.`attribute_id`
                    WHERE
                        `csa`.`product_id`=\"" . addslashes($productId) . "\"";
            $result = $this->getConnRead()->fetchAll($sql);

            if ($result !== false) {
                foreach ($result as $row) {
                    $this->_configurable_attributes_codes[$productId][$row['attribute_id']] = $row['attribute_code'];
                }
            }
        }

        return $this->_configurable_attributes_codes[$productId];
    }

    public function explodeMultiselectValue($value)
    {
        $arr = array();
        if (!empty($value)) {
            $arr = explode(',', $value);
            foreach ($arr as $k => $v) {
                $arr[$k] = trim($v);
            }
        }
        return $arr;
    }

    /**
     * @return Mage_Core_Model_Resource
     */
    public function getRes()
    {
        if (is_null($this->_res)) {
            $this->_res = Mage::getSingleton('core/resource');
        }
        return $this->_res;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getConnRead()
    {
        if (is_null($this->_conn_read)) {
            $this->_conn_read = $this->getRes()->getConnection('core_read');
        }
        return $this->_conn_read;
    }

    /**
     * @return Varien_Db_Adapter_Pdo_Mysql
     */
    public function getConnWrite()
    {
        if (is_null($this->_conn_write)) {
            $this->_conn_write = $this->getRes()->getConnection('core_write');
        }
        return $this->_conn_write;
    }

    /**
     * This method returns an attribute instance of RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Attribute
     * which incorporates destructor to free the memory
     *
     * @param  $code
     * @return mixed|null
     */
    public function getAttribute($attributeCode)
    {
        // Regular way of getting the attribute
        // return Mage::getModel('catalog/product')->getResource()->getAttribute($attributeCode);

        // Return from cache
        if (array_key_exists($attributeCode, $this->_attributes)) {
            return $this->_attributes[$attributeCode];
        }

        $entity = Mage::getModel('catalog/product')->getResource();
        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');

        $attributeInstance = Mage::getModel('googlebasefeedgenerator/resource_eav_attribute')->loadByCode($entityType, $attributeCode)
            ->setAttributeCode($attributeCode)
            ->setEntityType($entityType);

        if (!$attributeInstance->getAttributeCode()) {
            $attributeInstance->setAttributeCode($attributeCode)
                ->setBackendType(Mage_Eav_Model_Entity_Attribute_Abstract::TYPE_STATIC)
                ->setIsGlobal(1)
                ->setEntity($entity)
                ->setEntityType($entityType)
                ->setEntityTypeId($entityType->getId());
        }

        if (empty($attributeInstance)
            || !($attributeInstance instanceof Mage_Eav_Model_Entity_Attribute_Abstract)
            || (!$attributeInstance->getId()
                && !in_array($attributeInstance->getAttributeCode(), array('entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id', Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD)))
        ) {
            return false;
        }

        if (!$attributeInstance->getAttributeCode()) {
            $attributeInstance->setAttributeCode($attributeCode);
        }
        if (!$attributeInstance->getAttributeModel()) {
            $attributeInstance->setAttributeModel(Mage_Eav_Model_Entity::DEFAULT_ATTRIBUTE_MODEL);
        }

        // Construct a simplified version of attribute object
        $attribute = new Varien_Object();
        $attribute->setData(
            array(
                'id' => $attributeInstance->getId(),
                'attribute_id' => $attributeInstance->getAttributeId(),
                'attribute_code' => $attributeInstance->getAttributeCode(),
                'frontend_input' => $attributeInstance->getFrontendInput(),
                'backend_type' => $attributeInstance->getBackendType(),
                'frontend' => $attributeInstance->getFrontend(),
                'source_model' => $attributeInstance->getSourceModel(),
                'backend_model' => $attributeInstance->getBackendModel(),
                'label' => $attributeInstance->getLabel()
            )
        );

        // Cache the attribute object
        $this->_attributes[$attributeCode] = $attribute;

        // Release memory from original heavy attribute instance
        unset($attributeInstance); // RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Attribute::__destruct will be called

        return $this->_attributes[$attributeCode];
    }

    /**
     * Setting attributes
     * @param $attribute
     */
    public function setAttribute($attribute)
    {
        $this->_attributes[$attribute->getAttributeCode()] = $attribute;
    }

    /**
     * Adds extra data for configurable attribute (raw version of configurable type instance)
     *
     * @param  $attribute_code
     * @return mixed|null
     */
    public function getConfigurableAttribute($attribute_code, $product_id = null)
    {
        $attribute = $this->getAttribute($attribute_code);

        $row = array();
        $websiteId = $this->getGenerator($attribute->getStoreId())->getData('website_id');

        $sql = "SELECT `p`.`pricing_value`, `p`.`value_index`, `p`.`is_percent`
                FROM " . $this->getRes()->getTableName('catalog/product_super_attribute_pricing') . " AS `p`
                INNER JOIN " . $this->getRes()->getTableName('catalog/product_super_attribute') . " AS `s`
                    ON ( `s`.`product_super_attribute_id` = `p`.`product_super_attribute_id` ";
        if (!is_null($product_id)) {
            $sql .= "AND `s`.`product_id` = '" . $product_id . "' ";
        }
        $sql .= "AND `s`.`attribute_id` = " . (int)$attribute->getId() . ")";

        // Load website specific values
        if ($websiteId) {
            $row = $this->getConnRead()->fetchAll($sql . " WHERE `p`.`website_id` = " . (int)$websiteId);
        }

        // If no website specific, load global values
        if (!count($row)) {
            $row = $this->getConnRead()->fetchAll($sql);
        }

        $attribute->setData('values', $row);
        return $attribute;
    }

    /**
     * Get configurable attribute in Array format (raw version of configurable type instance)
     *
     * @param  $product
     * @return array
     */
    public function getConfigurableAttributesAsArray($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if (!array_key_exists($product->getId(), $this->_configurable_attributes_array)) {
            $attributeCodes = $this->getOptionCodes($product->getId());
            foreach ($attributeCodes as $code) {
                $values = $this->getConfigurableAttribute($code, $product->getId());
                if (count($values->getValues())) {
                    $this->_configurable_attributes_array[$product->getId()][] = $values;
                }
            }
        }

        return !array_key_exists($product->getId(), $this->_configurable_attributes_array) ? array() : $this->_configurable_attributes_array[$product->getId()];
    }

    /**
     * Free memory for a product
     * @param null $product
     * @return $this
     */
    public function unsConfigurableAttributesAsArray($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        if (array_key_exists($product->getId(), $this->_configurable_attributes_array)) {
            unset($this->__configurable_attributes_array[$product->getId()]);
        }
        return $this;
    }

    /**
     * Call the magento's clearInstance on versions it is defined
     *
     * @param $obj
     */
    public function clearNestedObject($obj)
    {
        if (method_exists($obj, 'clearInstance')) {
            $obj->clearInstance(); // clearInstance does a $product->getData('stock_item')->reset()
        }
        unset($obj);
    }

    /**
     * Set the $map price cache by $assoc product. Used with configurable products.
     *
     * @param  $map
     * @param  $assoc
     * @return bool
     */
    public function setCacheAssociatedPricesByProduct($map, $assoc)
    {
        $price = $this->getAssociatedPrice($map, $assoc, floatval($map->getProduct()->getPrice()));
        if ($price) {
            $map->setCacheAssociatedPricesByProduct($assoc, $price);
            return true;
        }

        return false;
    }

    public function getAssociatedPrice($parentMap, $assocProduct, $base_price)
    {
        $price = $base_price;
        $product = $parentMap->getProduct();

        $has_option_price = false;
        $configurable_attributes = $this->getConfigurableAttributesAsArray($product);

        if (!Mage::getSingleton('googlebasefeedgenerator/config')->isSimplePricingEnabled($this->getData('store_id'))
            && $price > 0) {
            if (is_array($configurable_attributes)) {
                foreach ($configurable_attributes as $res) {
                    if (is_array($res['values']) && count($res['values'])) {
                        $has_option_price = true;
                        foreach ($res['values'] as $value) {
                            if (isset($value['value_index']) && $assocProduct->getData($res['attribute_code']) == $value['value_index']) {
                                $option_price = floatval($value['pricing_value']);
                                if (isset($value['is_percent']) && $value['is_percent']) {
                                    $price += $base_price * $option_price / 100;
                                } else {
                                    $price += $option_price;
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        $assocProduct->setData('option_price', $has_option_price);
        if ((!$has_option_price && count($configurable_attributes)) || Mage::getSingleton('googlebasefeedgenerator/config')->isSimplePricingEnabled($this->getData('store_id')) ) {
            $price = floatval($assocProduct->getPrice());
        }

        return $price;
    }

    /**
     * Singleton by $storeId of generator class
     *
     * @param  $store_id
     * @return RocketWeb_GoogleBaseFeedGenerator_Model_Generator
     */
    public function getGenerator($store_id)
    {
        $registryKey = '_singleton/googlebasefeedgenerator/generator_store_' . $store_id;

        if (!Mage::registry($registryKey)) {
            Mage::register($registryKey, Mage::getModel('googlebasefeedgenerator/generator', $this->getData()));
        }

        return Mage::registry($registryKey);
    }

    /**
     * Generates a lock file with possible duplicated items in the feed.
     *
     * @param $row
     * @param $is_assoc_configurable
     * @param $is_assoc_grouped
     */
    public function lockDuplicates($row, $is_assoc_configurable, $is_assoc_grouped)
    {
        $skip = false;
        $generator = $this->getGenerator($this->getStoreId());
        $collection = $generator->getCollection();

        // Skip any associated products of configurable
        if (!empty($is_assoc_configurable)
            && $generator->isProductTypeEnabled(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            && $generator->getConfig()->isAllowConfigurableAssociatedMode($this->getStoreId())
            && $generator->getConfig()->getConfigVar('auto_skip')
        ) {

            $parents = array();
            foreach ($is_assoc_configurable as $assoc) {
                // check for parent existence in collection
                if (!is_null($collection->getItemById($assoc['parent_entity_id'])) && !in_array($assoc['parent_entity_id'], $parents)) {
                    $parents[$assoc['parent_entity_id']] = $assoc['parent_sku'];
                }
            }

            if ($parents) {
                if ($generator->getConfigVar('log_skip', 'file')) {
                    $prods_msg = ': ';
                    foreach ($parents as $id => $sku) {
                        $prods_msg .= sprintf('[SKU %s, ID %d]', $sku, $id) . (end($parents) ? '' : ',');
                    }
                    $generator->log(sprintf("Stand alone SKU %s, ID %d is been omitted because is part of configurable products %s", $row['sku'], $row['entity_id'], $prods_msg));
                }
                $skip = true;
            }
        }

        // Skip any associated products of grouped
        if (!empty($is_assoc_grouped)
            && $generator->isProductTypeEnabled(Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
            && $generator->getConfig()->isAllowGroupedAssociatedMode($this->getStoreId())
            && $generator->getConfig()->getConfigVar('auto_skip')
        ) {

            $parents = array();
            foreach ($is_assoc_grouped as $assoc) {
                // check for parent existence in collection
                if (!is_null($collection->getItemById($assoc['parent_entity_id'])) && !in_array($assoc['parent_entity_id'], $parents)) {
                    $parents[$assoc['parent_entity_id']] = $assoc['parent_sku'];
                }
            }
            if ($parents) {
                if ($generator->getConfigVar('log_skip', 'file')) {
                    $prods_msg = '';
                    foreach ($parents as $id => $sku) {
                        $prods_msg .= sprintf('[SKU %s, ID %d]', $sku, $id) . (end($parents) ? '' : ',');
                    }
                    $generator->log(sprintf("Stand alone SKU %s, ID %d is been omitted because is part of grouped product %s.", $row['sku'], $row['entity_id'], $prods_msg));
                }
                $skip = true;
            }
        }

        // Save them in processes list for later tests during processing of their parent
        Mage::getModel('googlebasefeedgenerator/process')->setData(
            array(
                'store_id' => $this->getStoreId(),
                'item_id' => $row['entity_id'],
                'status' => RocketWeb_GoogleBaseFeedGenerator_Model_Process::STATUS_PENDING
            )
        )->initialize();

        return $skip;
    }

    /**
     * Executes with each feed run; for batch mode only at the beginning of the process.
     * @return int
     */
    public function clearProcess()
    {
        return $this->getConnWrite()->exec('TRUNCATE table `' . Mage::getSingleton('core/resource')->getTableName('googlebasefeedgenerator/process') . '`');
    }
}