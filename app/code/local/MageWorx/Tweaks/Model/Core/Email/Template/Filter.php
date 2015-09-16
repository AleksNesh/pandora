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
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Tweaks extension
 *
 * @category   MageWorx
 * @package    MageWorx_Tweaks
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_Tweaks_Model_Core_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    public function translateDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['text'])){
            return Mage::helper('tweaks')->__($params['text']);
        }
        if (isset($params['block_id'])){
            $block = Mage::app()->getLayout()->createBlock('cms/block');
            if ($block){
                list($lang) = explode('_', Mage::getStoreConfig('general/locale/code'));
                $html = $block->setBlockId($params['block_id'] . '_' . $lang)->toHtml();
                if (empty($html)){
                    $html = $block->setBlockId($params['block_id'])->toHtml();
                }
                return $html;
            }
        }
    }
}
