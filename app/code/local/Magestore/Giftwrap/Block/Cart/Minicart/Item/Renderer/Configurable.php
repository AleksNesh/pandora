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
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magestore_Giftwrap_Block_Cart_Minicart_Item_Renderer_Configurable extends Mage_Checkout_Block_Cart_Item_Renderer {

    public function getOptionList() {
        $options = parent::getOptionList();
        $item = $this->getItem();

        $giftwrapItem = Mage::getModel('giftwrap/selectionitem')
                ->getCollection()
                ->addFieldToFilter('item_id', $item->getId())
                ->getFirstItem();
        $giftbox = Mage::getModel('giftwrap/selection')
                ->load($giftwrapItem->getSelectionId());
        $giftwrap = Mage::getModel('giftwrap/giftwrap')
                ->load($giftbox->getStyleId());
        $giftcard = Mage::getModel('giftwrap/card')
                ->load($giftbox->getGiftcardId());
        if ($giftwrapItem->getId()) {
            $options[] = array(
                'label' => Mage::helper('giftwrap')->__('Gift Wrap'),
                'value' => $this->htmlEscape($giftwrap->getTitle()),
            );
            if ($giftcard->getId()) {
                $options[] = array(
                    'label' => Mage::helper('giftwrap')->__('Gift Card'),
                    'value' => $this->htmlEscape($giftcard->getName()),
                );
            }
            if ($giftbox->getMessage()) {
                $options[] = array(
                    'label' => Mage::helper('giftwrap')->__('Gift Message'),
                    'value' => $this->htmlEscape($giftbox->getMessage()),
                );
            }
        }
        return $options;
    }

}
