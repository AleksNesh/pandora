<?php
/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Ash_Slideshow_Block_Adminhtml_Widget_Grid_Column_Renderer_Checkbox
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $values = $this->getColumn()->getValues();

        $value  = $row->getData($this->getColumn()->getIndex());
        if (is_array($values)) {
            $checked = in_array($value, $values) ? ' checked="checked"' : '';
        } else {
            $checked = ($value === $this->getColumn()->getValue()) ? ' checked="checked"' : '';
        }

        /**
         * Custom to check if asset has a relation with the slideshow
         */
        $slideId = $this->getRequest()->getParam('id');

        if($values === 'slide_custom_check') {
           $collection = Mage::getModel('ash_slideshow/slideshowasset')->getCollection();
           $collection->clear();
           $collection->getSelect()->where('asset_id = ?', $value);
           $collection->getSelect()->where('slide_id = ?', $slideId);
           $collection->getFirstItem();

           /**
            * This clone is because when you call getFirstItem()
            * (or just about any other retrieval method) on a collection
            * that collection is loaded. Any subsequent operation ignores
            * the database and uses only the loaded data, filters have no
            * effect because they are SQL only
            */

           /**
            * The clear() method unloads the data for that collection,
            * forcing it to access the database again next time.
            */
            $secondCollection = clone $collection;
            $secondCollection->clear();
            $secondCollection->toArray();
            $relationship = $secondCollection->getFirstItem()->getData();
            if(empty($relationship)) {
                $checked = '';
            } else {
                $checked = ' checked="checked"';
            }
        }

        if($values === 'asset_custom_check') {
            $collection = Mage::getModel('ash_slideshow/slideshowasset')->getCollection();
            $collection->clear();
            $collection->getSelect()->where('asset_id = ?', $slideId);
            $collection->getSelect()->where('slide_id = ?', $value);
            $collection->getFirstItem();

            /**
             * This clone is because when you call getFirstItem()
             * (or just about any other retrieval method) on a collection
             * that collection is loaded. Any subsequent operation ignores
             * the database and uses only the loaded data, filters have no
             * effect because they are SQL only
             */

            /**
             * The clear() method unloads the data for that collection,
             * forcing it to access the database again next time.
             */
            $secondCollection = clone $collection;
            $secondCollection->clear();
            $secondCollection->toArray();
            $relationship = $secondCollection->getFirstItem()->getData();
            if(empty($relationship)) {
                $checked = '';
            } else {
                $checked = ' checked="checked"';
            }
        }

        $disabledValues = $this->getColumn()->getDisabledValues();
        if (is_array($disabledValues)) {
            $disabled = in_array($value, $disabledValues) ? ' disabled="disabled"' : '';
        } else {
            $disabled = ($value === $this->getColumn()->getDisabledValue()) ? ' disabled="disabled"' : '';
        }

        $this->setDisabled($disabled);

        if ($this->getNoObjectId() || $this->getColumn()->getUseIndex()) {
            $v = $value;
        } else {
            $v = ($row->getId() != "") ? $row->getId():$value;
        }

        return $this->_getCheckboxHtml($v, $checked);
    }

    /**
     * @param string $value   Value of the element
     * @param bool   $checked Whether it is checked
     * @return string
     */
    protected function _getCheckboxHtml($value, $checked)
    {
        $html = '<input type="checkbox" ';
        $html .= 'name="' . $this->getColumn()->getFieldName() . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" ';
        $html .= 'class="'. ($this->getColumn()->getInlineCss() ? $this->getColumn()->getInlineCss() : 'checkbox') .'"';
        $html .= $checked . $this->getDisabled() . '/>';
        return $html;
    }
}
