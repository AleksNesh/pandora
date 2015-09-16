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
class Ash_HideEmptyAttributes_Helper_Output extends Mage_Catalog_Helper_Output
{
    const XML_PATH_ENABLED = 'ash_hideemptyattributes/general/enabled';

    /**
     * Attribute Frontend Input Types that we will check for valid options
     * @var array
     */
    protected $_inputTypesWithOptions   = array('select', 'multiselect', 'textarea');

    /**
     * Check if Ash_HideEmptyAttributes is enabled
     *
     * @return  boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Determine if the displaying of the attribute label/value
     * should be skipped and return a boolean value
     *
     * @param  string   $attrCode
     * @param  string   $value
     * @return boolean
     */
    public function shouldSkipDisplayOfAttribute($attrCode, $value)
    {
        // system configuration values
        $enabled        = $this->isEnabled();

        // check if the $value is a valid option for the attribute
        $isValidOption  = $this->isValidOptionForAttribute($attrCode, $value);

        if ($enabled) {
            $skip = ($isValidOption) ? false : true;
        } else {
            $skip = false;
        }

        return $skip;
    }

    /**
     * Check if the attribute value is indeed a valid option for the attribute
     *
     * Really, it's only checking select/multiselect attributes and options b/c
     * they are more prone to displaying values of 'N/A' or 'No' when no option
     * was selected.
     *
     * Attributes with frontend input types other than select or multiselect
     * we will just assume the value is a valid option as is with no further
     * checking.
     *
     * @param  string   $attrCode
     * @param  string   $value
     * @return boolean
     */
    public function isValidOptionForAttribute($attrCode, $value)
    {
        $frontendInputType  = $this->getAttributeFrontendInputType($attrCode);

        if(in_array($frontendInputType, $this->_inputTypesWithOptions)){
            $attrOptions    = $this->getAttributeOptions($attrCode);

            $options        = array();
            foreach($attrOptions as $option) {
                $options[] = $option['label'];
            }

            /**
             * if the value is within the attribute options then
             * it's a valid value for the attribute and we should
             * not skip the display of the attribute label and value
             */
            if ($frontendInputType === 'multiselect') {
                $multiValues = explode(', ', $value);
                $validCount = 0;
                foreach ($multiValues as $val) {
                    if (in_array($val, $options)) {
                        $validCount++;
                    }
                }

                // if more than one valid value found, assume it is okay value
                $valid = ($validCount > 0) ? true : false;
            } else {
                $valid = (in_array($value, $options)) ? true : false;
            }



        } else {
            /**
             * default to true for attributes that
             * are not select/multiselect inputs
             */
            $valid = true;
        }

        return $valid;
    }

    /**
     * Load an attribute from it's attribute_code
     *
     * @param  string   $attrCode
     * @param  string   $entityType
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute($attrCode, $entityType = Mage_Catalog_Model_Product::ENTITY)
    {
        return Mage::getSingleton('eav/config')->getAttribute($entityType, $attrCode);
    }

    /**
     * Return an array of the attribute's options if attribute supports options
     *
     * @param  string   $attrCode
     * @return array
     */
    public function getAttributeOptions($attrCode)
    {
        $attribute  = $this->getAttribute($attrCode);
        $options    = $attribute->getSource()->getAllOptions();

        return $options;
    }

    /**
     * Get the attribute's frontend input type:
     *
     * Typical options:
     *
     *     + text
     *     + textarea
     *     + date
     *     + boolean
     *     + multiselect
     *     + select
     *     + price
     *     + media_image
     *     + weee (Fixed Product Tax)
     *
     * @param  string   $attrCode
     * @return string
     */
    public function getAttributeFrontendInputType($attrCode)
    {
        $attribute = $this->getAttribute($attrCode);
        return $attribute->getData('frontend_input');
    }

}
