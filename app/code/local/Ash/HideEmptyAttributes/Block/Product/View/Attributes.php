<?php
/**
 * Ash_HideEmptyAttributes
 *
 * Skip listing of attributes if they have a 'NA' value
 *
 * @category    Ash
 * @package     Ash_HideEmptyAttributes
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Ash_HideEmptyAttributes_Block_Product_View_Attributes extends Mage_Catalog_Block_Product_View_Attributes
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ash_hideemptyattributes/catalog/product/view/attributes.phtml');
    }
}
