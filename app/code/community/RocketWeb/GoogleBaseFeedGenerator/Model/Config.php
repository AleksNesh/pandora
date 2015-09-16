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
 * Config data model
 *
 * @category RocketWeb
 * @package  RocketWeb_GoogleBaseFeedGenerator
 */
class RocketWeb_GoogleBaseFeedGenerator_Model_Config extends Mage_Core_Model_Config_Data
{

    CONST XML_PATH_RGBF = 'rocketweb_googlebasefeedgenerator';
    const MAIN_LICENSE_FILE = 'rw-gsf-license.txt';
    const LICENSE_CHECK_URL = 'http://www.rocketweb.com/license/index/check/';

    protected $_product_attributes_codes = null;
    protected $_product_directives = null;
    protected $_cache_categories = array();
    protected $_store_categories = array();
    protected $_cache_catgories_tree = null;
    protected $_cache_catgories_tree_ids = null;
    protected $_shipping_territory = null;
    protected $_config_cache = array();

    /**
     * Check to see if module is enabled and if we have a valid license file
     * @return bool
     */
    public function isEnabled($store_id = null)
    {
        $on = $this->getConfigVar('is_turned_on', $store_id);

        $license = file_exists(Mage::getModuleDir('etc', 'RocketWeb_GoogleBaseFeedGenerator') . '/license.txt');
        if (!$license) {
            $license = file_exists($this->_getMainLicenseFilePath());
        }

        return (bool)($on && $license);
    }

    protected function _getMainLicenseFilePath()
    {
        return Mage::getBaseDir('var') . '/' . self::MAIN_LICENSE_FILE;
    }

    /**
     * Activates a license key the first time you save it
     * Tied to event ''
     *
     * @return bool
     */
    public function activateLicense(Varien_Event_Observer $observer)
    {
        $config = $observer->getEvent()->getObject();
        if ($config->getSection() !== 'rocketweb_googlebasefeedgenerator') {
            return;
        }

        $old_key = $this->getConfigVar('license_key');
        $new_key = $config->groups['file']['fields']['license_key']['value'];

        if (empty($new_key) && !empty($old_key)) {
            //delete existing license
            unlink($this->_getMainLicenseFilePath());
            Mage::getSingleton('adminhtml/session')->addWarning('Google Shopping Feed: License was removed, extension is now inactive');
            return false;
        }

        if ($old_key == $new_key) {
            return true;
        }

        $domain = str_replace(array('http://', 'https://'), array('', ''), Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB));
        $parts = explode('/', trim($domain));
        $domain = $parts[0];
        $params = array(
            'key' => $new_key,
            'domain' => $domain
        );
        $params_string = '';
        foreach ($params as $key => $value) {
            $params_string .= $key . '=' . $value . '&';
        }
        rtrim($params_string, '&');

        $ch = curl_init();

        curl_setopt_array(
            $ch, array(
                CURLOPT_URL => self::LICENSE_CHECK_URL,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $params_string
            )
        );

        $response = json_decode(curl_exec($ch));
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response instanceof stdClass && $response->valid) {
            $license_file = fopen($this->_getMainLicenseFilePath(), 'w');
            fclose($license_file);

            if ($license_file === false) {
                Mage::getSingleton('adminhtml/session')->addError('Google Shopping Feed: License is not activated (could not save license file)');
                unlink($this->_getMainLicenseFilePath());
                return false;
            }

            Mage::getSingleton('adminhtml/session')->addSuccess('Google Shopping Feed: License is activated! You can now generate feeds.');
            return true;
        } else {
            Mage::log('Google Shopping Feed - License activation error: ' . $response->error . ' (' . $http_status . ')');
            Mage::getSingleton('adminhtml/session')->addError('Google Shopping Feed: License is not activated (license key is not valid)');
            unlink($this->_getMainLicenseFilePath());
            return false;
        }
    }

    /**
     * Shortcut to Mage::getStoreConfig()
     *
     * @param  string $key
     * @param  mixed $store_id
     * @param  string $section
     * @return mixed
     */
    public function getConfigVar($key, $store_id = null, $section = 'file')
    {
        // grab it from cache
        if (array_key_exists($key . '_' . $section . '_' . $store_id, $this->_config_cache)) {
            return $this->_config_cache[$key . '_' . $section . '_' . $store_id];
        }

        switch ($key) {
            case "map_product_columns":
                $ret = $this->getConfigVarMapProductColumns($key, $store_id, $section);
                break;
            default:
                $ret = Mage::getStoreConfig(self::XML_PATH_RGBF . '/' . $section . '/' . $key, $store_id);
        }

        $this->_config_cache[$key . '_' . $section . '_' . $store_id] = $ret;
        return $ret;
    }

    /**
     * Shortcut to config values with frontend type multiselect.
     *
     * @param  string $key
     * @param  mixed $store_id
     * @param  string $section
     * @return mixed
     */
    public function getConfigVarMapProductColumns($key, $store_id = null, $section = 'file')
    {
        $ret = Mage::getStoreConfig(self::XML_PATH_RGBF . '/' . $section . '/' . $key, $store_id);
        if (empty($ret)) {
            $ret = $this->convertDefaultMapProductColumns($store_id);
        }

        // no order -> last
        foreach ($ret as $k => $arr) {
            if (!isset($arr['order']) || (isset($arr['order']) && $arr['order'] == "")) {
                $ret[$k]['order'] = 99999;
            }
        }

        return $ret;
    }

    /**
     * Converts config var default_map_product_columns to format of backend type serialized array.
     *
     * @param  int $store_id
     * @return array
     */
    public function convertDefaultMapProductColumns($store_id = null)
    {
        $ret = array();
        $default_map_product_columns = $this->getConfigVar('default_map_product_columns', $store_id, 'general');
        foreach ($default_map_product_columns as $atrib => $arr) {
            $ret[] = array(
                'column' => $arr['column'],
                'attribute' => $atrib,
                'param' => (isset($arr['param']) ? $arr['param'] : ''),
                'order' => (isset($arr['order']) ? $arr['order'] : ''),
            );
        }
        return $ret;
    }

    /**
     * @param string $key
     * @param mixed $store_id
     * @param string $section
     * @return array()
     */
    public function getMultipleSelectVar($key, $store_id = null, $section = 'file')
    {
        $str = $this->getConfigVar($key, $store_id, $section);
        $arr = array();
        if (!empty($str)) {
            $arr = explode(",", $str);
        }
        $carr = $arr;
        foreach ($carr as $k => $v) {
            if ($v === "") {
                unset($arr[$k]);
            }
        }
        return $arr;
    }

    /**
     * Crates array of attributes and directives for dropdowns.
     * [code] => [label]
     *
     * @param  int $store_id
     * @param  bool $with_directives
     * @return array
     */
    public function getProductAttributesCodes($store_id = null, $with_directives = true)
    {
        $config_gbase = Mage::getSingleton('googlebasefeedgenerator/config');
        $exclude_attributes = $config_gbase->getMultipleSelectVar('exclude_attributes', $store_id, 'general');

        if (is_null($this->_product_attributes_codes)) {
            $this->_product_attributes_codes = array();

            $config = Mage::getModel('eav/config');
            $object = new Varien_Object(array('store_id' => $store_id));
            $attributes_codes = $config->getEntityAttributeCodes('catalog_product', $object);
            foreach ($attributes_codes as $attribute_code) {
                if (array_search($attribute_code, $exclude_attributes) !== false) {
                    continue;
                }
                $attribute = $config->getAttribute('catalog_product', $attribute_code);
                if ($attribute !== false && $attribute->getAttributeId() > 0) {
                    $this->_product_attributes_codes[$attribute->getAttributeCode()] = addslashes($attribute->getFrontend()->getLabel() . ' (' . $attribute->getAttributeCode() . ')');
                }
            }
            asort($this->_product_attributes_codes);
        }

        if ($with_directives === true && is_null($this->_product_directives)) {
            $this->_product_directives = array();

            $directives = $config_gbase->getConfigVar('directives', $store_id, 'general');
            foreach ($directives as $code => $arr) {
                $this->_product_directives[$code] = $arr['label'];
            }
        }

        $return = $this->_product_attributes_codes;

        if ($with_directives === true) {
            $return = array_merge($this->_product_directives, $this->_product_attributes_codes);
        }

        return $return;
    }

    /**
     * @param string $name
     * @param int $store_id
     * @return array
     */
    public function getMapCategorySorted($name, $store_id = null)
    {
        $map = $this->getConfigVar($name, $store_id, 'columns');
        $ret = array();
        if (empty($map)) {
            return array();
        }

        $order = array();
        $tt = array();
        foreach ($map as $k => $value) {
            if (isset($value['value']) && trim($value['value']) != "") {
                if (isset($value['order']) && $value['order'] != "") {
                    $order[$k] = $value['order'];
                } else {
                    $tt[$k] = "";
                }
            }
        }
        asort($order);

        foreach ($order as $k => $v) {
            $ret[$k] = $map[$k];
            if (isset($ret[$k]['value'])) {
                $ret[$k]['value'] = trim($ret[$k]['value']);
            }
        }
        foreach ($tt as $k => $v) {
            $ret[$k] = $map[$k];
        }

        return $ret;
    }

    /**
     * @param string $code
     * @return bool $code
     */
    public function isDirective($code, $store_id = null)
    {
        $directives = $this->getConfigVar('directives', $store_id, 'general');
        if (isset($directives[$code])) {
            return true;
        }
        return false;
    }

    public function getAllowedStockStatuses()
    {
        return array('in stock', 'out of stock', 'available for order', 'preorder');
    }

    public function getInStockStatus()
    {
        return 'in stock';
    }

    public function getOutOfStockStatus()
    {
        return 'out of stock';
    }

    public function getAllowedConditions()
    {
        return array('new', 'used', 'refurbished');
    }

    public function getConditionNew()
    {
        return 'new';
    }

    public function getAllowedGender()
    {
        return array('female', 'male', 'unisex');
    }

    public function getAllowedAgeGroup()
    {
        return array('adult', 'kids');
    }

    public function getAllowedSizeType()
    {
        return array('regular', 'petite', 'plus', 'big and tall', 'maternity');
    }

    public function getAllowedSizeSystem()
    {
        return array('US', 'UK', 'EU', 'DE', 'FR', 'JP', 'CN (China)', 'IT', 'BR', 'MEX', 'AU');
    }

    public function parseEmailsTo($str)
    {
        if (empty($str)) {
            return array();
        }
        $str = str_replace("\r", " ", $str);
        $emails = preg_split("/[\s,]+/", $str);

        return $emails;
    }

    /**
     * @param null $store_id
     * @return null
     */
    public function getAllCategories($store_id = null)
    {
        $store_id = !is_null($store_id) ? $store_id : 0;
        $rootId = $store_id ? Mage::app()->getStore($store_id)->getRootCategoryId() : 0;

        if (!array_key_exists($store_id, $this->_store_categories)) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store_id)
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSort('path', 'asc')
                ->addAttributeToFilter('name', array('neq' => ''))
                ->addAttributeToFilter('level', array('lt' => $this->getConfigVar('category_depth')));

            if ($rootId > 0) {
                $collection->addFieldToFilter('path', array('like' => "1/$rootId/%"));
            }
            $collection->addAttributeToFilter('is_active', 1);
            $collection->load();
            $this->_store_categories[$store_id] = $collection->toArray();
        }

        return $this->_store_categories[$store_id];
    }

    /**
     * Get category Tree and cache it
     * @return mixed
     */
    public function getCategoriesTree()
    {
        if (is_null($this->_cache_catgories_tree)) {

            $return = array();
            $names = array();
            /**
             * @var Mage_Catalog_Model_Resource_Category_Tree $tree
             */
            $tree = Mage::getModel('catalog/category')->getTreeModel()->load();

            foreach ($this->getAllCategories() as $categ) {

                if (array_key_exists('name', $categ)) {
                    $categ['name'] = addslashes($categ['name']);
                    $path = array();
                    $names[$categ['entity_id']] = $categ['name'];
                    $node = $tree->getNodeById($categ['entity_id']);
                    if (method_exists($node, 'getPath')) {
                        foreach ($node->getPath() as $item) {
                            if ($item->getLevel() > 1 && array_key_exists($item->getId(), $names)) {
                                array_unshift($path, $names[$item->getId()]);
                            }
                        }
                    }
                    $return[$categ['entity_id']] = implode(' > ', $path);
                }
            }

            $this->_cache_catgories_tree = $return;
        }

        return $this->_cache_catgories_tree;
    }

    /**
     * Get category Tree and cache it
     * @return mixed
     */
    public function getCategoriesTreeIds()
    {
        if (is_null($this->_cache_catgories_tree_ids)) {

            $return = array();
            $ids = array();
            /**
             * @var Mage_Catalog_Model_Resource_Category_Tree $tree
             */
            $tree = Mage::getModel('catalog/category')->getTreeModel()->load();

            foreach ($this->getAllCategories() as $categ) {

                if (array_key_exists('name', $categ)) {
                    $path = array();
                    $ids[$categ['entity_id']] = $categ['entity_id'];
                    $node = $tree->getNodeById($categ['entity_id']);
                    if (method_exists($node, 'getPath')) {
                        foreach ($node->getPath() as $item) {
                            if (array_key_exists($item->getId(), $ids)) {
                                array_unshift($path, $ids[$item->getId()]);
                            }
                        }
                    }
                    $return[$categ['entity_id']] = $path;
                }
            }

            $this->_cache_catgories_tree_ids = $return;
        }

        return $this->_cache_catgories_tree_ids;
    }

    public function isAllowConfigurableMode($store_id = null)
    {
        $ret = false;
        $cfg = $this->getConfigVar('associated_products_mode', $store_id, 'configurable_products');
        $allow = array(
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated_Mode::ONLY_PARENT,
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated_Mode::BOTH_PARENT_ASSOCIATED
        );
        if (array_search($cfg, $allow) !== false) {
            $ret = true;
        }
        return $ret;
    }

    public function isAllowConfigurableAssociatedMode($store_id = null)
    {
        $ret = false;
        $cfg = $this->getConfigVar('associated_products_mode', $store_id, 'configurable_products');
        $allow = array(
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated_Mode::ONLY_ASSOCIATED,
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Associated_Mode::BOTH_PARENT_ASSOCIATED);
        if (array_search($cfg, $allow) !== false) {
            $ret = true;
        }
        return $ret;
    }

    public function isAllowBundleMode($store_id = null)
    {
        $ret = false;
        $cfg = $this->getConfigVar('associated_products_mode', $store_id, 'bundle_products');
        $allow = array(
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Bundle_Associated_Mode::ONLY_BUNDLE,
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Bundle_Associated_Mode::BOTH_BUNDLE_ASSOCIATED
        );
        if (array_search($cfg, $allow) !== false) {
            $ret = true;
        }
        return $ret;
    }

    public function isAllowBundleAssociatedMode($store_id = null)
    {
        $ret = false;
        $cfg = $this->getConfigVar('associated_products_mode', $store_id, 'bundle_products');
        $allow = array(
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Bundle_Associated_Mode::ONLY_ASSOCIATED,
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Bundle_Associated_Mode::BOTH_BUNDLE_ASSOCIATED);
        if (array_search($cfg, $allow) !== false) {
            $ret = true;
        }
        return $ret;
    }

    public function isAllowGroupedMode($store_id = null)
    {
        $ret = false;
        $cfg = $this->getConfigVar('associated_products_mode', $store_id, 'grouped_products');
        $allow = array(
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Grouped_Associated_Mode::ONLY_GROUPED,
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Grouped_Associated_Mode::BOTH_GROUPED_ASSOCIATED);
        if (array_search($cfg, $allow) !== false) {
            $ret = true;
        }
        return $ret;
    }

    public function isAllowGroupedAssociatedMode($store_id = null)
    {
        $ret = false;
        $cfg = $this->getConfigVar('associated_products_mode', $store_id, 'grouped_products');
        $allow = array(
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Grouped_Associated_Mode::ONLY_ASSOCIATED,
            RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Grouped_Associated_Mode::BOTH_GROUPED_ASSOCIATED);
        if (array_search($cfg, $allow) !== false) {
            $ret = true;
        }
        return $ret;
    }

    public function isAllowProductOptions($store_id = null)
    {
        return $this->getConfigVar('product_options_mode', $store_id, 'file') == RocketWeb_GoogleBaseFeedGenerator_Model_Source_Product_Option::MULTIPLE_ROWS;
    }

    public function getShippingTerritory($store_id = null)
    {
        if (!is_null($this->_shipping_territory)) {
            return $this->_shipping_territory;
        }

        $this->_shipping_territory = array();
        $allowed_countries = $this->getShippingAllowedCountries($store_id);
        if (empty($allowed_countries)) {
            return $this->_shipping_territory;
        }
        $by_region = $this->getConfigVar('by_region', $store_id, 'shipping');
        $allowed_country_with_region = array();
        if ($by_region) {
            $allowed_country_with_region = $this->getMultipleSelectVar('country_with_region', $store_id, 'shipping');
            $allowed_country_with_region = array_intersect($allowed_country_with_region, $allowed_countries);
        }

        foreach ($allowed_countries as $country_id) {
            $this->_shipping_territory[$country_id] = array();
        }

        if (count($allowed_country_with_region) > 0) {
            $regionsCollection = Mage::getResourceModel('directory/region_collection')
                ->addCountryFilter($allowed_country_with_region)
                ->load();
            $countryRegions = array();
            foreach ($regionsCollection as $region) {
                $countryRegions[$region->getCountryId()][$region->getId()] = $region->getCode();
            }
            unset($regionsCollection);

            foreach ($allowed_country_with_region as $country_id) {
                $this->_shipping_territory[$country_id] = array();
                if ($by_region && isset($countryRegions[$country_id]) && count($countryRegions[$country_id] > 0)) {
                    $this->_shipping_territory[$country_id] = $countryRegions[$country_id];
                }
            }
        }

        return $this->_shipping_territory;
    }

    public function getShippingAllowedCountries($store_id = null)
    {
        return $this->getMultipleSelectVar('country', $store_id, 'shipping');
    }

    /**
     * Compares current version of Magento.
     *
     * @param array $info like the one in Mage::getVersionInfo()
     * @param string $operator Operators: >= > <= < != ==
     */
    public function compareMagentoVersion($info, $operator = null)
    {
        $ret = false;
        if (!is_array($info)) {
            return false;
        }
        if (is_null($operator)) {
            $operator = ">=";
        }
        $cinfo = Mage::getVersionInfo();
        $keys = array('major', 'minor', 'revision', 'patch');
        $c = $cv = 0;
        $i = 4;
        foreach ($keys as $key) {
            $cv += intval($cinfo[$key]) * pow(10, --$i);
        }
        $i = 4;
        foreach ($keys as $key) {
            if (!isset($info[$key])) {
                return false;
            }
            $c += intval($info[$key]) * pow(10, --$i);
        }
        switch ($operator) {
            case '>=':
                if ($cv >= $c) {
                    $ret = true;
                }
                break;
            case '>':
                if ($cv > $c) {
                    $ret = true;
                }
                break;
            case '<':
                if ($cv <= $c) {
                    $ret = true;
                }
                break;
            case '<=':
                if ($cv < $c) {
                    $ret = true;
                }
                break;
            case '!=':
                if ($cv != $c) {
                    $ret = true;
                }
                break;
            case '==':
                if ($cv == $c) {
                    $ret = true;
                }
                break;

            default:
                if ($cv >= $c) {
                    $ret = true;
                }
        }

        return $ret;
    }

    /**
     * Get an array of all product categories from given store_id
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $store_id
     * @return array
     */
    public function getProductCategoriesByStore(Mage_Catalog_Model_Product $product, $store_id) {
        $rootCategoryId = Mage::app()->getStore($store_id)->getRootCategoryId();

        $collection = $product->getCategoryCollection()
            ->addFieldToFilter('is_active', 1)
            ->setStoreId($store_id)
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"));

        return $collection;
    }

    /**
     * This method returns a string containing the category of a
     * product according to the path of the category starting from
     * the root category (if the product has it assigned), up to the
     * specific category assigned to the product.
     * e.g.: Home > Garden > Flowers > Roses
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     * @author RocketWeb
     */
    public function getProductCategoryTree(Mage_Catalog_Model_Product $product, $params = array())
    {
        $product = $product->load($product->getId());
        $categories = $this->getProductCategoriesByStore($product, $params['store_id']);

        if ($categories->getSize() <= 0) {
            return '';
        }

        $removes = array('default', 'root');

        /*
         * Build category path for each of product categories
         */
        $return = array();
        foreach ($categories as $cat_info) {

            $names = array();
            // Loop through each items of the path
            $pItemsPieces = explode('/', $cat_info['path']);

            foreach ($pItemsPieces AS $id) {

                if (!array_key_exists($id, $this->_cache_categories)) {
                    $category = Mage::getModel('catalog/category')->setStoreId($params['store_id'])->load($id);
                    $this->_cache_categories[$id] = trim($category->getName());
                }
                $category_name = $this->_cache_categories[$id];

                if (empty($category_name)) {
                    continue;
                }

                $skip_node = false;
                foreach ($removes as $value) {
                    if (strstr(strtolower($category_name), strtolower(trim($value))) !== false) {
                        $skip_node = true;
                    }
                }

                if (!$skip_node) {
                    array_push($names, $category_name);
                }
            }

            // Implode the result items
            $return[implode(' > ', $names)] = count($names);
        }

        // Sort and Merge all paths
        arsort($return);
        return array_keys($return);
    }

    /**
     * Returns ids of categories included in the feed, false for all categories
     * @return int[]|false
     */
    public function getIncludedCategoryIds($store_id)
    {
        $config = $this->getConfigVar('category_tree_include', $store_id, 'filters');
        if (empty($config)) {
            return false;
        } else {
            return array_map('intval', explode(',', $config));
        }
    }

    /**
     * Returns ids of categories with products that will variate custom options, false for all categories
     * @return int[]|false
     */
    public function getOptionCategoryIds($store_id)
    {
        $config = $this->getConfigVar('vary_categories', $store_id, 'product_options');
        if (empty($config)) {
            return false;
        } else {
            return array_map('intval', explode(',', $config));
        }
    }

    /**
     * Returns array of attribute set ids to include in the feed
     *
     * @param  int $store_id
     * @return int[]|false
     */
    public function getAttributeSets($store_id)
    {
        $config = $this->getConfigVar('attribute_sets', $store_id, 'filters');
        if (empty($config)) {
            return false;
        } else {
            $config = trim($config, ', ');
            return array_map('intval', explode(',', $config));
        }
    }

    public function isSimplePricingEnabled($store_id) {
        $scp = Mage::helper('googlebasefeedgenerator')->isModuleEnabled('OrganicInternet_SimpleConfigurableProducts');

        $aya = Mage::helper('googlebasefeedgenerator')->isModuleEnabled('Ayasoftware_SimpleProductPricing')
            && Mage::getStoreConfig('spp/setting/enableModule', $store_id);

        return $scp || $aya;
    }

}