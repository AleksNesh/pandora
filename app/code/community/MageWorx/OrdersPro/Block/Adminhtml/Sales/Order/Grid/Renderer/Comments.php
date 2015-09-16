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
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
class MageWorx_OrdersPro_Block_Adminhtml_Sales_Order_Grid_Renderer_Comments extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        /** @var MageWorx_OrdersPro_Helper_Data $helper */
        $helper = Mage::helper('mageworx_orderspro');
        $comments = explode("\n", $this->htmlEscape($row->getData($this->getColumn()->getIndex())));
        $comments = array_reverse($comments);
        $limit = $helper->getNumberComments();
        if ($limit > 0) array_splice($comments, $limit);

        if (strpos(Mage::app()->getRequest()->getRequestString(), '/exportCsv/')) {
            return implode('|', $comments);
        }

        $count = count($comments);
        $prefix = 'c';

        if ($count > 3) {
            $comments[$count - 1] .= '<a href="" onclick="$(\'hdiv_' . $row->getData('increment_id') . '_' . $prefix . '\').style.display=\'none\'; $(\'a_' . $row->getData('increment_id') . '_' . $prefix . '\').style.display=\'block\'; return false;" style="float:right; font-weight:bold; text-decoration: none;" title="' . $helper->__('Less..') . '">↑</a>'
                . '</div>'
                . '<a href="" id="a_' . $row->getData('increment_id') . '_' . $prefix . '" onclick="$(\'hdiv_' . $row->getData('increment_id') . '_' . $prefix . '\').style.display=\'block\'; this.style.display=\'none\'; return false;" style="float:right; font-weight:bold; text-decoration: none;" title="' . $helper->__('More..') . '">↓</a>';
            $comments[2] .= '<div id="hdiv_' . $row->getData('increment_id') . '_' . $prefix . '" style="display:none">' . $comments[3];
            unset($comments[3]);
        }
        return '<div style="cursor: text">' . implode('<br/>', $comments) . '</div><a/>';
    }
}