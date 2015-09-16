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
 */

/**
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @author     RocketWeb
 */

/**
 * @var $installer RocketWeb_GoogleBaseFeedGenerator_Model_Resource_Eav_Mysql4_Setup
 */
$installer = $this;
$installer->startSetup();

// Migration definitions
$path_to_path = array(
    'rocketweb_googlebasefeedgenerator/settings/is_turned_on' => 'rocketweb_googlebasefeedgenerator/file/is_turned_on',
    'rocketweb_googlebasefeedgenerator/settings/license_key' => 'rocketweb_googlebasefeedgenerator/file/license_key',
    'rocketweb_googlebasefeedgenerator/settings/locale' => 'rocketweb_googlebasefeedgenerator/file/locale',
    'rocketweb_googlebasefeedgenerator/settings/feed_dir' => 'rocketweb_googlebasefeedgenerator/file/feed_dir',
    'rocketweb_googlebasefeedgenerator/settings/use_batch_segmentation' => 'rocketweb_googlebasefeedgenerator/file/use_batch_segmentation',
    'rocketweb_googlebasefeedgenerator/settings/batch_limit' => 'rocketweb_googlebasefeedgenerator/file/batch_limit',
    'rocketweb_googlebasefeedgenerator/settings/auto_skip' => 'rocketweb_googlebasefeedgenerator/file/auto_skip',

    'rocketweb_googlebasefeedgenerator/settings/add_out_of_stock' => 'rocketweb_googlebasefeedgenerator/filters/add_out_of_stock',
    'rocketweb_googlebasefeedgenerator/settings/category_tree_include' => 'rocketweb_googlebasefeedgenerator/filters/category_tree_include',
    'rocketweb_googlebasefeedgenerator/settings/product_types' => 'rocketweb_googlebasefeedgenerator/filters/product_types',
    'rocketweb_googlebasefeedgenerator/settings/attribute_sets' => 'rocketweb_googlebasefeedgenerator/filters/attribute_sets',
    'rocketweb_googlebasefeedgenerator/columns/map_replace_empty_columns' => 'rocketweb_googlebasefeedgenerator/filters/map_replace_empty_columns',
    'rocketweb_googlebasefeedgenerator/settings/find_and_replace' => 'rocketweb_googlebasefeedgenerator/filters/find_and_replace',
    'rocketweb_googlebasefeedgenerator/columns/skip_column_empty' => 'rocketweb_googlebasefeedgenerator/filters/skip_column_empty',

    'rocketweb_googlebasefeedgenerator/settings/associated_products_mode' => 'rocketweb_googlebasefeedgenerator/configurable_products/associated_products_mode',
    'rocketweb_googlebasefeedgenerator/settings/add_out_of_stock_configurable_assoc' => 'rocketweb_googlebasefeedgenerator/configurable_products/add_out_of_stock',
    'rocketweb_googlebasefeedgenerator/settings/inherit_parent_out_of_stock' => 'rocketweb_googlebasefeedgenerator/configurable_products/inherit_parent_out_of_stock',
    'rocketweb_googlebasefeedgenerator/columns/associated_products_description' => 'rocketweb_googlebasefeedgenerator/configurable_products/associated_products_description',
    'rocketweb_googlebasefeedgenerator/columns/associated_products_link' => 'rocketweb_googlebasefeedgenerator/configurable_products/associated_products_link',
    'rocketweb_googlebasefeedgenerator/columns/associated_products_image_link_configurable' => 'rocketweb_googlebasefeedgenerator/configurable_products/associated_products_image_link',
    'rocketweb_googlebasefeedgenerator/columns/associated_products_link_add_unique' => 'rocketweb_googlebasefeedgenerator/configurable_products/associated_products_link_add_unique',
    'rocketweb_googlebasefeedgenerator/apparel/attribute_merge_value_separator' => 'rocketweb_googlebasefeedgenerator/configurable_products/attribute_merge_value_separator',

    'rocketweb_googlebasefeedgenerator/settings/grouped_associated_products_mode' => 'rocketweb_googlebasefeedgenerator/grouped_products/associated_products_mode',
    'rocketweb_googlebasefeedgenerator/settings/add_out_of_stock_grouped_assoc' => 'rocketweb_googlebasefeedgenerator/grouped_products/add_out_of_stock',
    'rocketweb_googlebasefeedgenerator/columns/grouped_price_display_mode' => 'rocketweb_googlebasefeedgenerator/grouped_products/price_display_mode',
    'rocketweb_googlebasefeedgenerator/columns/grouped_associated_products_description' => 'rocketweb_googlebasefeedgenerator/grouped_products/associated_products_description',
    'rocketweb_googlebasefeedgenerator/columns/grouped_associated_products_link' => 'rocketweb_googlebasefeedgenerator/grouped_products/associated_products_link',
    'rocketweb_googlebasefeedgenerator/columns/grouped_associated_products_image_link' => 'rocketweb_googlebasefeedgenerator/grouped_products/associated_products_image_link',
    'rocketweb_googlebasefeedgenerator/columns/grouped_associated_products_link_add_unique' => 'rocketweb_googlebasefeedgenerator/grouped_products/associated_products_link_add_unique',
);

$directive_to_path = array(
    'rw_gbase_directive_expiration_date' => 'rocketweb_googlebasefeedgenerator/columns/ttl',
    'rw_gbase_directive_price' => 'rocketweb_googlebasefeedgenerator/columns/add_tax_to_price',
    'rw_gbase_directive_sale_price' => 'rocketweb_googlebasefeedgenerator/columns/add_tax_to_price',
    'rw_gbase_directive_id' => 'rocketweb_googlebasefeedgenerator/columns/id_store_code',
    'rw_gbase_directive_url' => 'rocketweb_googlebasefeedgenerator/columns/add_to_product_url',
    'rw_gbase_directive_quantity' => 'rocketweb_googlebasefeedgenerator/settings/qty_mode',
);
$column_to_path = array(
    'weight' => 'rocketweb_googlebasefeedgenerator/columns/weight_unit_measure',
    'color' => 'rocketweb_googlebasefeedgenerator/apparel/color_attribute_code',
    'size' => 'rocketweb_googlebasefeedgenerator/apparel/size_attribute_code',
    'age_group' => 'rocketweb_googlebasefeedgenerator/apparel/age_group_attribute_code',
    'gender' => 'rocketweb_googlebasefeedgenerator/apparel/gender_attribute_code',
    'pattern' => 'rocketweb_googlebasefeedgenerator/apparel/variant_pattern_attribute_code',
    'material' => 'rocketweb_googlebasefeedgenerator/apparel/variant_material_attribute_code',
    'size_type' => 'rocketweb_googlebasefeedgenerator/apparel/size_type_attribute_code',
    'size_system' => 'rocketweb_googlebasefeedgenerator/apparel/size_system_attribute_code'
);

$map_to_path = array_merge($directive_to_path, $column_to_path);

// Migrate old direct config paths
foreach ($path_to_path as $old_path => $new_path) {
    $sql = "UPDATE `{$this->getTable('core_config_data')}` SET path = '{$new_path}' WHERE path = '{$old_path}'";
    try {
        $installer->run($sql);
    }
    catch (Exception $e) {
        Mage::log("Upgrade error: Could not port old config key: {$old_path} to {$new_path}: {$e->getMessage()}");
    }
}

// correct locale values.
$locale_conf = $installer->getConnection()->fetchAll("SELECT * FROM `{$this->getTable('core_config_data')}` WHERE path = 'rocketweb_googlebasefeedgenerator/file/locale'");
foreach ($locale_conf as $row) {
    $installer->run("UPDATE `{$this->getTable('core_config_data')}` SET value = '". str_replace('_', '-', $row['value']). "' WHERE config_id = '{$row['config_id']}'");
}

// False values would remove the row
$replace_directives = array('rw_gbase_directive_adwords_price_buckets' => 'rw_gbase_directive_price_buckets',
                            'rw_gbase_directive_condition' => 'rw_gbase_directive_static_value',
                            'rw_gbase_directive_adwords_labels' => false,
                            'rw_gbase_directive_adwords_grouping' => false);

// update columns map rules
$column_map_rows = $installer->getConnection()->fetchAll("SELECT * from `{$this->getTable('core_config_data')}` WHERE path = 'rocketweb_googlebasefeedgenerator/columns/map_product_columns'");
$column_map_rows = $installer->updateColumnMap($column_map_rows, $map_to_path);
$installer->replaceDirectives($column_map_rows, $replace_directives);

// update replace empty rules
$add_rules = array(
    array('column' => 'google_product_category', 'attribute' => 'rw_google_base_product_categ'),
    array('column' => 'google_product_category', 'attribute' => 'rw_gbase_directive_google_category_by_category'),
    array('column' => 'product_type', 'attribute' => 'rw_google_base_product_type'),
    array('column' => 'product_type', 'attribute' => 'rw_gbase_directive_product_type_by_category'),
    array('column' => 'product_type', 'attribute' => 'rw_gbase_directive_product_type_magento_category')
);
$replace_empty_rows = $installer->getConnection()->fetchAll("SELECT * FROM `{$this->getTable('core_config_data')}` WHERE path = 'rocketweb_googlebasefeedgenerator/filters/map_replace_empty_columns'");
$replace_empty_rows = $installer->updateReplaceEmptyMap($replace_empty_rows, $map_to_path, $add_rules);
$installer->replaceDirectives($replace_empty_rows, $replace_directives);

$installer->endSetup();