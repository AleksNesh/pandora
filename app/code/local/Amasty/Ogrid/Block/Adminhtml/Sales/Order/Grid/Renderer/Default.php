<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Ogrid
*/
class Amasty_Ogrid_Block_Adminhtml_Sales_Order_Grid_Renderer_Default extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_orderTableAlias = 'am_order_item';
    public function render(Varien_Object $row)
    {
        $maxItems =  intval(Mage::getStoreConfig('amogrid/general/max'));
        $isOnlySimple =  intval(Mage::getStoreConfig('amogrid/general/configurable')) == 1;
        
        $entityId = $row->getData('entity_id');

        $index = $this->getColumn()->getIndex();
        $index = str_replace("{$this->_orderTableAlias}.", '', $index);
        $collection = Mage::getModel("amogrid/order_item")->getCollection();
        
        $collection->getSelect()->join(
            array(
                'orderItem' => $collection->getTable('sales/order_item')
            ),
            'main_table.item_id = orderItem.item_id', 
            array('orderItem.qty_ordered', 'orderItem.is_qty_decimal')
        );
        
        $collection->addFieldToFilter("orderItem.order_id", $entityId);
        
        
        if ($isOnlySimple){
            $collection->addFieldToFilter("orderItem.product_type", array('eq' => 'simple'));
        }
                
        $html = array();
        
        $data = $collection->getData();
            
        foreach($data as $order => $item){
            $ordered = intval($item['is_qty_decimal']) > 0 ? number_format($item['qty_ordered'], 2) : number_format($item['qty_ordered']);
            
            $val = empty($item[$index]) ? '-' : $item[$index];
            $html[] = '<div class='.(count($data) > 1 ? "am_order_row" : "").'>'.$this->htmlEscape($val).
                    ($index == 'name' ? ' (' . $ordered . ')' : '') .
                    '</div>';
        }
        
        $val = count($html) > 0 ? implode("", $html) : '';
        
        if (count($html) > $maxItems){
            $htmlSl = array_slice($html, 0, $maxItems);
            
            $val = implode("", $htmlSl);
            
            $url = $this->getUrl('amogrid/adminhtml_index/view', array(
                'order_id' => $entityId
            ));
            
            $val .= '<a href="'.$url.'" onclick="return orderLoadMore(this, \''.$index.'\');" class="am_load_more">more...</a>';
        }
        
        return $val;
    }
}