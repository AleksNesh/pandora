<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Model_Order_Item extends Mage_Core_Model_Abstract
{
    protected $_attributesData = array();
    protected $_attributes = array();
    
    public function _construct()
    {
        $this->_init('amogrid/order_item');
    }
    
    public function getMappedColumns(){
        $predefinedColumns = array(
            'ogrid_item_id', 'item_id'
        );
        
        $tableName = Mage::getSingleton('core/resource')
                ->getTableName('amogrid/order_item');
        
        $read = Mage::getSingleton('core/resource')
                ->getConnection('core_read');
        
        $readresult = $read->query("SHOW COLUMNS FROM $tableName");
        
        $columns = array();
        
        while ($row = $readresult->fetch() ) {
            if (!in_array($row['Field'], $predefinedColumns))
                $columns[] = $row['Field'];
        }
        
        return $columns;
    }
    
    public function getAttributes()
    {
        $types = array('text', 'select', 'multiselect', 'boolean', 'textarea', 'price', 'weight');
        $excludedAttributes = array('custom_design', 'custom_design_from', 'custom_design_to');
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();
        
        $collection->getSelect()->where(
            $collection->getConnection()->quoteInto('main_table.frontend_input IN (?)', $types)
        );
        
        $collection->getSelect()->where(
            $collection->getConnection()->quoteInto('main_table.attribute_code NOT IN (?)', $excludedAttributes)
        );

        return $collection;
    }
    
    protected function getFilteredColumns(array $codes){
        $requestedColumns = array();
        
        $productCollection = $this->getAttributes();
        
        $productCollection->getSelect()->where(
            $productCollection->getConnection()->quoteInto('main_table.attribute_code IN (?)', $codes)
        );
        
        $collectionItems = $productCollection->getItems();
        
        foreach($collectionItems as $item){
        
            foreach($codes as $code){
                if (!isset($requestedColumns[$code]) && $item->getName() == $code){
                    $requestedColumns[$code] = $code;
                }
            }
        }
        
        return $requestedColumns;
    }
    
    protected function getRemoveColumns($requestedColumns, $mappedColumns){
        $removeColumns = array();
        
        foreach($mappedColumns as $mappedColumn){
            if (!in_array($mappedColumn, $requestedColumns)){
                $removeColumns[]= $mappedColumn;
            }
        }
        
        return $removeColumns;
    }
    
    protected function getMappingColumns($requestedColumns, $mappedColumn){
        $mappingColumns = array();
        
        foreach($requestedColumns as $requestedColumn){
            if (!in_array($requestedColumn, $mappedColumn)){
                $mappingColumns[] = $requestedColumn;
            }
        }
        
        return $mappingColumns;
    }
    
    protected function alter(array $mappingColumns, array $removeColumns){
        if (count($mappingColumns) > 0 || count($removeColumns) > 0){
            $tableName = Mage::getSingleton('core/resource')
                    ->getTableName('amogrid/order_item');

            $alter_tpl = 'alter table `:table` :alters';
            $add_column_tpl = 'add column `:name` varchar(255)';
            $add_key_tpl = 'add key `KEY_:name` (:name)';
            
            $drop_column_tpl = 'drop column `:name`';
            $drop_key_tpl = 'drop key `KEY_:name`';

            $alters = array();

            foreach($mappingColumns as $column){    
                $alters[] = strtr($add_column_tpl, array(
                    ":name" => $column
                ));
                
                $alters[] = strtr($add_key_tpl, array(
                    ":name" => $column
                ));
            }
            
            foreach($removeColumns as $column){
                 $alters[] = strtr($drop_key_tpl, array(
                    ":name" => $column
                ));
                
                $alters[] = strtr($drop_column_tpl, array(
                    ":name" => $column
                ));
            }

            $query = strtr($alter_tpl, array(
                ":table" => $tableName,
                ":alters" => implode(", ", $alters)
            ));

            $write = Mage::getSingleton('core/resource')->getConnection('core_write');
            $write->query($query);
        }
    }
    
    protected function getAttributesData(array $codes, $store_id)
    {
        if (!isset($this->_attributesData[$store_id])){
            $attributesData = array();

            $attributesCollection = Mage::getResourceModel('catalog/product_attribute_collection')
                    ->addVisibleFilter()
                    ->addStoreLabel($store_id);

            $attributesCollection->getSelect()->where(
                $attributesCollection->getConnection()->quoteInto('main_table.attribute_code IN (?)', $codes)
            );

            $attributes = $attributesCollection->getItems();

            foreach($attributes as $attribute){

                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
                        
                switch ($attribute->getFrontendInput()){
                    case "select":
                    case "multiselect":
                    case "boolean":
                        $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                ->setAttributeFilter($attribute->getId())
                                ->setStoreFilter($store_id, false)
                                ->load();

                        if ($valuesCollection->getSize() > 0)
                        {
                            foreach ($valuesCollection as $item) {

                               $attributesData[$attribute->getAttributeCode()][$item->getId()] = $item->getValue();
                            }
                        } 
                        else 
                        {
                            $selectOptions = $attribute->getFrontend()->getSelectOptions();
                            
                            if ($selectOptions)
                            {
                                foreach ($selectOptions as $selectOption)
                                { 
                                   $attributesData[$attribute->getAttributeCode()][$selectOption['value']] = $selectOption['label'];    
                                }
                            }
                        }
                        break;
                    case "custom_design":
                        $allOptions = $attribute->getSource()->getAllOptions();

                        if (is_array($allOptions) && !empty($allOptions))
                        {
                            foreach ($allOptions as $option)
                            {
                                if (!is_array($option['value']))
                                {
                                    if ($option['value'])
                                    {
                                      $attributesData[$attribute->getAttributeCode()][$option['value']] = $option['value'];
                                    }
                                } 
                                else 
                                {
                                    foreach ($option['value'] as $option2)
                                    {
                                        if (isset($option2['value']))
                                        {
                                            $attributesData[$attribute->getAttributeCode()][$option2['value']] = $option2['value'];
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
            
            $this->_attributesData[$store_id] = $attributesData;
            
        }
        return $this->_attributesData[$store_id];
    }
    
    public function clearTemporaryData(){
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $orderItemProductTableName = Mage::getSingleton('core/resource')
            ->getTableName('amogrid/order_item_product');
        
        $truncateQuery = 'delete from '.$orderItemProductTableName;

        $write->query($truncateQuery);
    }
    
    protected function initProduct2OrderItemRelation($ordersIds = array(), $fullRemap = FALSE, $orderItem = NULL){
        $orderItemTableName = Mage::getSingleton('core/resource')
            ->getTableName('sales/order_item');
        
        $orderItemProductTableName = Mage::getSingleton('core/resource')
            ->getTableName('amogrid/order_item_product');
        
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $this->clearTemporaryData();
        
        $insertQuery = NULL;
        
        if ($fullRemap){
            $insertQuery = '
                INSERT INTO ' . $orderItemProductTableName . ' (item_id, product_id, store_id)
                SELECT item_id, product_id, store_id FROM ' . $orderItemTableName;
            $write->query($insertQuery);
        } 
        
        
        if ($orderItem !== NULL){
            $insertQuery = '
                INSERT INTO ' . $orderItemProductTableName . ' (item_id, product_id, store_id) VALUES
                ('.$orderItem->getItemId().', ' . $orderItem->getProductId() . ', ' . intval($orderItem->getStoreId()) . ' )
            ';

            $write->query($write->quoteInto($insertQuery, $ordersIds));
        }
        
        if (count($ordersIds) > 0){
            $insertQuery = '
                INSERT INTO ' . $orderItemProductTableName . ' (item_id, product_id, store_id)
                SELECT item_id, product_id, store_id FROM ' . $orderItemTableName . '
                WHERE order_id IN (?)
            ';

            $write->query($write->quoteInto($insertQuery, $ordersIds));
        }
    }
    
    protected function getMappingData($mappingColumns){

        $orderItemProductTableName = Mage::getSingleton('core/resource')
            ->getTableName('amogrid/order_item_product');

        $collection = Mage::getModel('catalog/product')->getCollection();
        
        $collection->getSelect()->join( 
            array(
                'orderItemProducts' => $orderItemProductTableName
            ),
            'e.entity_id = orderItemProducts.product_id', 
            array('orderItemProducts.*')
        );
        
        $collection->getSelect()->group('e.entity_id');

        $collection->addAttributeToSelect($mappingColumns);

        foreach($mappingColumns as $attribute){
            if ($attribute != 'sku')
                $collection->joinAttribute($attribute, 'catalog_product/' . $attribute, 'entity_id', null, 'left', Mage::app()->getStore()->getId());
        }
        
        $mappingData = $collection->getData();

        $retMappingData = array();
        
        foreach($mappingData as &$mappingItem){
            $retMappingData[$mappingItem['product_id']] = &$mappingItem;
        }
        
        return $retMappingData;
    }
    
    protected function applyAttributesPerStore($mappingItem, $store_id, $mappingColumns){
        $ret = $mappingItem;
        
        $attributesData = $this->getAttributesData($mappingColumns, $store_id);

        foreach($mappingColumns as $mappingColumn){
            if (isset($mappingItem[$mappingColumn])){

                if (isset($attributesData[$mappingColumn])){

                    $attributeOptionId = $mappingItem[$mappingColumn];

//                    if (isset($attributesData[$mappingColumn][$attributeOptionId])){
                        
                        if (isset($this->_attributes[$mappingColumn])){
                            
                            if ($this->_attributes[$mappingColumn]->getFrontendInput() == "multiselect"){
                                $ids = explode(",", $attributeOptionId);
                                $value = array();
                                foreach ($ids as $id){
                                    if (is_numeric($id) && isset( $attributesData[$mappingColumn][$id])){
                                        $value[] = $attributesData[$mappingColumn][$id];
                                    }
                                }
                                $ret[$mappingColumn] = implode(",", $value);
                            } else {
                                if (isset($attributesData[$mappingColumn][$attributeOptionId]))
                                    $ret[$mappingColumn] = $attributesData[$mappingColumn][$attributeOptionId];
                            }
                        }
                        
//                    }
                }
            }
        }
        
        return $ret;
        
    }
    
    protected function callMap(array $mappingColumns, array $mappingData){
//        if (count($mappingColumns) > 0 && count($mappingData) > 0){
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $insert_tpl = 'insert into :name (:columns) VALUES :values :on_duplicate_update';

            $columns = array_merge(array('item_id'), $mappingColumns);

            $orderItemProductTableName = Mage::getSingleton('core/resource')
                ->getTableName('amogrid/order_item_product');

             $orderItemTableName = Mage::getSingleton('core/resource')
                ->getTableName('amogrid/order_item');

            $selectQuery = '
                select item_id, product_id, store_id from ' . $orderItemProductTableName . '
            ';

            $readresult=$write->query($selectQuery);
            
            $rows = array();
            
            while ($item = $readresult->fetch() ) {
                $store_id = $item["store_id"];
                $productId = $item["product_id"];
                $item_id = $item["item_id"];

                if (isset($mappingData[$productId])){

                    $mappingItem = $this->applyAttributesPerStore($mappingData[$productId], $store_id, $mappingColumns);
                    
                    $vals = array($item_id);

                    foreach($mappingColumns as $mappingColumn){
                        $vals[] = isset($mappingItem[$mappingColumn]) ?
                                $mappingItem[$mappingColumn] :
                                '';
                    }

                    $rows[] = $write->quoteInto('(?)', $vals);
                }
            }

            $onDuplicateUpdate = array();
            foreach($mappingColumns as $mappingColumn){
                $onDuplicateUpdate[] = "`$mappingColumn` = VALUES(`$mappingColumn`)";
            }
            
            $rows4insert = array();
            $maxRowsPerIteration = 1000;
            
            foreach($rows as $row){
                $rows4insert[] = $row;
                if (count($rows4insert) > $maxRowsPerIteration){
                    $insertQuery = strtr($insert_tpl, array(
                        ':name' => $orderItemTableName,
                        ':columns' => '`'. implode('`, `', $columns) .'`',
                        ':values' => implode(', ', $rows4insert),
                        ':on_duplicate_update' => count($onDuplicateUpdate) > 0 ? ' ON DUPLICATE KEY UPDATE '.implode(', ', $onDuplicateUpdate) : ' ON DUPLICATE KEY UPDATE item_id = VALUES(item_id)'
                    ));

                    $write->query($insertQuery);
                    
                    $rows4insert = array();
                }
                
            }
            
            if (count($rows4insert) > 0){
            $insertQuery = strtr($insert_tpl, array(
                ':name' => $orderItemTableName,
                ':columns' => '`'. implode('`, `', $columns) .'`',
                ':values' => implode(', ', $rows4insert),
                ':on_duplicate_update' => count($onDuplicateUpdate) > 0 ? ' ON DUPLICATE KEY UPDATE '.implode(', ', $onDuplicateUpdate) : ' ON DUPLICATE KEY UPDATE item_id = VALUES(item_id)'
            ));
            $write->query($insertQuery);
            }
            

            
//        }
    }

    function mapData(array $codes, $ordersIds = array(), $fullRemap = FALSE) {
        $this->initProduct2OrderItemRelation($ordersIds, $fullRemap);

        $requestedColumns = $this->getFilteredColumns($codes);

        $mappedColumn = $this->getMappedColumns();
        $mappingColumns = $this->getMappingColumns($requestedColumns, $mappedColumn);
        $removeColumns = $this->getRemoveColumns($requestedColumns, $mappedColumn);
        
        $this->alter($mappingColumns, $removeColumns);

        $mappingData = $this->getMappingData($mappingColumns);

        $this->callMap($mappingColumns, $mappingData);
    }
    
    function mapOrder($orderItem){
        $this->initProduct2OrderItemRelation(array(), FALSE, $orderItem);
        $mappedColumn = $this->getMappedColumns();
        $mappingData = $this->getMappingData($mappedColumn);
        $this->callMap($mappedColumn, $mappingData);
    }
    
    function getUnmappedOrders(){
        $collection = Mage::getResourceModel('sales/order_item_collection');
        
        $collection->getSelect()->join(
            array(
                'order_grid' => $collection->getTable('sales/order_grid')
            ),
            'order_grid.entity_id = main_table.order_id', 
            array('order_grid.increment_id', 'order_grid.entity_id as order_id')
        );
        
        $collection->getSelect()->join(
            array(
                'am_order_item_product' => $collection->getTable('amogrid/order_item_product')
            ),
            'main_table.item_id = am_order_item_product.item_id', 
            array()
        );
        
        $collection->getSelect()->joinLeft(
            array(
                'product' => $collection->getTable('catalog/product')
            ),
            'product.entity_id = am_order_item_product.product_id', 
            array()
        );
        
        $collection->getSelect()->where(
            'product.entity_id IS NULL'
        );
        
        $collection->getSelect()->limit(10);
        $collection->getSelect()->order('main_table.order_id desc');
        
        return $collection;
    }
}
?>