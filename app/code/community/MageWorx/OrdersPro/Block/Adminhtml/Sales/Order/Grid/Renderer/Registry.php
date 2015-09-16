<?php

/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Renderer_Registry extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $index = $this->getColumn()->getIndex();
        switch ($index) {
            case 'method':
                $registry = 'payment_methods';
                break;
            case 'shipping_method':
                $registry = 'shipping_methods';
                break;
            case 'customer_group_id':
                $registry = 'customer_groups';
                break;
            case 'shipped':
                $registry = 'shipped_statuses';
                break;
            case 'order_group_id':
                $registry = 'order_groups';
                break;
            case 'is_edited':
                $registry = 'edited_statuses';
                break;
            default :
                return '';
        }
        $id = $row->getData($index);
        $values = Mage::registry($registry);

        if ($index == 'shipping_method' && $row->getData('shipping_description')) return $row->getData('shipping_description');

        if (isset($values[$id])) return $this->htmlEscape($values[$id]);

        if ($index == 'shipping_method' && strpos($id, '_')) {
            $id = explode('_', $id);
            $id2 = $id[0] . '_' . $id[0];
            unset($id[0]);
            if (isset($values[$id2]))
                return $this->htmlEscape($values[$id2] . ' ' . implode('_', $id));
        }
        return $id;
    }

}
