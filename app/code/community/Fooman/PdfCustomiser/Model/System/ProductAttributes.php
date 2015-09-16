<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_ProductAttributes
{
    /**
     * supply dropdown choices for custom product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $collection = Mage::getResourceModel('catalog/product_attribute_collection');
        foreach ($collection as $attribute) {
            $options[] = array(
                'value'=> $attribute->getAttributeCode(),
                'label'=> ($attribute->getFrontendLabel() ? $attribute->getFrontendLabel()
                    : $attribute->getAttributeCode())
            );

        }
        $options[] = array(
            'value'=> 'category_ids',
            'label'=> 'Category'
        );
        return $options;
    }
}
