<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_GoogleBaseFeedGenerator
 * @copyright Copyright (c) 2015 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    RocketWeb
 */

class RocketWeb_GoogleBaseFeedGenerator_Model_Map_Option extends Mage_Core_Model_Abstract
{
    protected $_items = array(), $_prices = array();
    private $_item = array(), $_row = array();

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Return the last price by option id used
     *
     * @param $key
     * @return int
     */
    protected function _getPrice($key)
    {
        return array_key_exists($key, $this->_prices) ? $this->_prices[$key] : 0;
    }

    /**
     * Process current product rows and append or update them according to options usage
     *
     * @param $rows
     * @return array
     */
    public function process($rows)
    {
        if (empty($rows)){
            return array();
        }

        foreach ($rows as $row) {
            $this->_item = $this->_row = $row;
            $options = $this->getOptions();
            $this->_processItems($options, count($options));
        }

        $items = $this->getItems();
        return !empty($items) ? $items : $rows;
    }

    /**
     * Returns an array of product options used in the column map across all columns.
     * If $names provided, it will only add those that are within those names.
     *
     * @param array $names
     * @return array
     */
    public function getOptions($names = array())
    {
        $return = array();
        $map = $this->getMap();

        $options = $map->getProduct()->getOptions();
        if (count($options)) {
            if (empty($names)) {
                // Process the options used in the columns map
                foreach ($map->getGenerator()->getColumnsMap() as $column) {
                    if ($column['attribute'] == 'rw_gbase_directive_product_option') {
                        $names[$column['column']] = is_array($column['param']) ? $column['param'] : array($column['param']);
                    }
                }
            }

            foreach ($options as $option) {
                foreach ($names as $column => $name) {
                    if (in_array($option->getTitle(), $name)) {
                        $option->setFeedColumn($column);
                        $return[$option->getId()] = $option;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Recursively extract the generate the option items from options array
     *
     * @param $options array of options used in the column map, obtain with getOptions
     * @param $deepth the number of option to fill in the row
     * @param array $fill counter for
     */
    protected function _processItems($options, $deepth, $fill = array()) {

        // Loop through available options
        foreach ($options as $id => $option) {

            // remove current option so it does not get processed again
            unset($options[$id]);

            $values = $option->getValues();
            foreach ($values as $value) {

                // update the option column
                $this->_item[$option->getFeedColumn()] = $value->getTitle();

                // increment the item price with option price
                $this->_prices[$option->getId()] = Mage::helper('core')->currency($value->getPrice(true), false, false);
                $this->_updateItemPrice();

                // update the item ID
                $this->_updateItemId($value);

                // add auto-select parameters to the link
                $this->_updateItemLink(array($option->getGroupByType(). '_'. $option->getId()  => $value->getId()));

                // append the item to the list only if all options have been filled.
                $fill[$option->getFeedColumn()] = true;
                if (!count($options) && count($fill) == $deepth) {
                    $this->_items[] = $this->_item;
                } else {
                    $this->_processItems($options, $deepth, $fill);
                }
            }

            // reset for new option, looping through values need to retain item data.
            $fill = array();
        }
    }

    /**
     * @return $this
     */
    protected function _updateItemPrice()
    {
        if (array_key_exists('price', $this->_row)
            && $this->_row['price'] > 0
            && array_key_exists('price', $this->_item)) {

            $this->_item['price'] = $this->_row['price'];
            foreach ($this->_prices as $p) {
                $this->_item['price'] += $p;
            }
        }

        if (array_key_exists('sale_price', $this->_item)
            && $this->_row['sale_price'] > 0
            && array_key_exists('sale_price', $this->_row)) {

            $this->_item['sale_price'] = $this->_row['sale_price'];
            foreach ($this->_prices as $p) {
                $this->_item['sale_price'] += $p;
            }
        }

        return $this;
    }

    /**
     * Update the ID by appending an increment
     * Update the SKU with the option sku if provided, else append increment
     * Update item group id to be the sku of initial product
     *
     * @param $value Mage_Catalog_Model_Product_Option_Value
     * @return $this
     */
    protected function _updateItemId($value)
    {
        $map = $this->getMap();
        foreach ($map->getGenerator()->getColumnsMap() as $column) {

            switch ($column['attribute']) {
                case 'rw_gbase_directive_id':
                    $this->_item[$column['column']] = $value->getProduct()->getId(). '-option'. count($this->_items);
                    break;
                case 'sku':
                    if (!is_null($value->getSku())) {
                        $this->_item[$column['column']] = $value->getSku();
                    } else {
                        $this->_item[$column['column']] = $this->_row[$column['column']]. '-'. (count($this->_items)+1);
                    }
                    break;
                case 'rw_gbase_directive_item_group_id':
                    $this->_item[$column['column']] = $value->getProduct()->getSku();
                    break;
            }
        }
        return $this;
    }

    /**
     * @param $params
     * @return $this
     */
    protected function _updateItemLink($params)
    {
        if (!array_key_exists('link', $this->_item)) {
            return $this;
        }

        $parts = parse_url($this->_item['link']);

        // append old params
        if (isset($parts['query'])) {
            parse_str($parts['query'], $old_params);
            foreach ($old_params as $key => $value) {
                if (!array_key_exists($key, $params)) {
                    $params[$key] = $value;
                }
            }
        }

        $parts['query'] = http_build_query($params);

        $link = "";
        foreach ($parts as $k => $v) {
            switch ($k) {
                case 'scheme':
                    $link .= $v . '://';
                    break;
                case 'port':
                    $link .= ':' . $v;
                    break;
                case 'query':
                    $link .= '?' . $v;
                    break;
                case 'fragment':
                    $link .= '#' . $v;
                    break;
                default:
                    $link .= $v;
            }
        }

        $this->_item['link'] = $link;
        return $this;
    }
}