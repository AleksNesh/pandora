<?php
/**
 * Open Commerce LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Commerce LLC Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.opencommercellc.com/license/commercial-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@opencommercellc.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future.
 *
 * @category   OpenCommerce
 * @package    OpenCommerce_OrderEdit
 * @copyright  Copyright (c) 2013 Open Commerce LLC
 * @license    http://store.opencommercellc.com/license/commercial-license
 */
class TinyBrick_OrderEdit_Block_Adminhtml_Sales_Order_Edit_Search_Grid_Renderer_Product extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	/**
	 * Render product name to add Configure link
	 *
	 * @param   Varien_Object $row
	 * @return  string
	 */
	public function render(Varien_Object $row)
	{
		$rendered       =  parent::render($row);
		$isConfigurable = $row->canConfigure();
		$style          = $isConfigurable ? '' : 'style="color: #CCC;"';
		$prodAttributes = $isConfigurable ? sprintf('list_type = "product_to_add" product_id = %s', $row->getId()) : 'disabled="disabled"';
		return sprintf('<a href="javascript:void(0)" %s class="f-right" %s>%s</a>',
				$style, $prodAttributes, Mage::helper('sales')->__('Configure')) . $rendered;
	}
}