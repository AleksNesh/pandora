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
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */


/**
 * Adminhtml system config attributes array field renderer
 *
 * @category   RocketWeb
 * @package    RocketWeb_GoogleBaseFeedGenerator
 */
class RocketWeb_GoogleBaseFeedGenerator_Block_Adminhtml_System_Config_Form_Field_Mapproductcolumns extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    public function __construct()
    {
        $this->addColumn('order', array(
            'label' => Mage::helper('adminhtml')->__('Order'),
            'style' => 'width:30px',
            'class' => 'validate-greater-than-zero grid-order',
        ));

        $this->addColumn('column', array(
            'label' => Mage::helper('adminhtml')->__('Feed Column'),
            'style' => 'width:200px',
        ));

        $this->addColumn('attribute', array(
            'label' => Mage::helper('adminhtml')->__('Map To'),
            'style' => 'width:300px',
        ));

        $this->addColumn('param', array(
            'label' => Mage::helper('adminhtml')->__('Options'),
            'style' => 'min-width:320px',
            'class' => 'input-text'
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Column');
        parent::__construct();
        $this->setTemplate('googlebasefeedgenerator/system/config/form/field/array.phtml');

        // Set parameters to be replaced in the frontend row template
        $this->setGroupName('columns');
        $this->setFieldName('map_product_columns');
    }

    /**
     * Forms array for select values: attribute_code => attribute_label.
     *
     * @return array
     */
    protected function getProductAttributesCodes()
    {
        $store_id = null;
        if (($store_code = $this->getRequest()->getParam('store')) != "") {
            $store_id = Mage::app()->getStore($store_code)->getStoreId();
        }
        $ret = Mage::getSingleton('googlebasefeedgenerator/config')->getProductAttributesCodes($store_id);
        foreach ($ret as $key => $value) {
            $ret[$key] = addslashes($value);
        }
        return $ret;
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
    protected function _renderCellTemplate($columnName)
    {
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($column['renderer']) {
            return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
                ->toHtml();
        }

        $html = '';

        switch ($columnName) {
            case 'column':
                $html .= $this->_renderColumnCell($columnName);
                break;
            case 'order':
                $html .= $this->_renderTextCell($columnName);
                break;
            case 'attribute':
                $html .= '<select name="' . $inputName . '" ' . (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '>';
                $html .= '<optgroup label="--------------- ' . $this->__('Directives') . ' ---------------"></optgroup>';
                $optg = false;
                foreach ($this->getProductAttributesCodes() as $value => $label) {
                    if (!$optg && strpos($label, "(") !== false) {
                        $html .= '<optgroup label="--------------- ' . $this->__('Attributes') . ' ---------------"></optgroup>';
                        $optg = true;
                    }
                    $html .= '<option label="' . $label . '" value="' . $value . '">' . $label . '</option>';
                }
                $html .= '</select>';
                break;
        }

        return $html;
    }

    /**
     * This method gets overwritten in Block_Adminhtml_System_Config_Form_Field_Mapemptycolumns
     * @param $columnName
     * @return string
     */
    protected function _renderColumnCell($columnName)
    {
        return $this->_renderTextCell($columnName);
    }

    /**
     * @param $columnName
     * @return string
     */
    protected function _renderTextCell($columnName)
    {
        $column = $this->_columns[$columnName];
        $inputName = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        return '<input type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
            ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
            (isset($column['class']) ? $column['class'] : 'input-text') . '"' .
            (isset($column['style']) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }

    /**
     * @return string
     */
    public function configToJson()
    {
        $store_id = null;
        if (($store_code = $this->getRequest()->getParam('store')) != "") {
            $store_id = Mage::app()->getStore($store_code)->getStoreId();
        }
        $config = Mage::getSingleton('googlebasefeedgenerator/config');

        $default_columns = $config->getConfigVar('default_map_product_columns', $store_id, 'general');
        $directives = $config->getConfigVar('directives', $store_id, 'general');

        $source_models = array();
        foreach ($directives as $directive => $obj) {
            if (array_key_exists('source_model', $obj)) {
                $model = Mage::getModel($obj['source_model']);
                if (array_key_exists('param', $obj)) {
                    $model->setParam($obj['param']);
                }
                $source_models[$directive] = $model->toHtml();
            }
        }

        return Zend_Json::encode(
            array('attributes' => $default_columns,
                  'directives' => $directives,
                  'source' => $source_models)
        );
    }

    /**
     * @param $column
     * @return bool
     */
    public function allowFillParams($column)
    {
        return $column == 'attribute';
    }

    /**
     * Overwritten to remove label cell if label is empty
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();
        $label = trim($element->getLabel());
        $html = '<tr id="row_' . $id . '">';
        if (!empty($label)) {
            $html .= '<td class="label"><label for="' . $id . '">' . $element->getLabel() . '</label></td>';
        }

        //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType() === 'multiple';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        } elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit() == 1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        $html .= empty($label) ? '<td colspan="2" class="value">' : '<td class="value">';
        $html .= $this->_getElementHtml($element);
        if ($element->getComment()) {
            $html .= '<p class="note"><span>' . $element->getComment() . '</span></p>';
        }
        $html .= '</td>';

        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k => $v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif ($v['value'] == $defText) {
                        $defTextArr[] = $v['label'];
                        break;
                    }
                }
                $defText = join(', ', $defTextArr);
            }

            // default value
            $html .= '<td class="use-default">';
            $html .= '<input id="' . $id . '_inherit" name="' . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" ' . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html .= '<label for="' . $id . '_inherit" class="inherit" title="' . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
            $html .= '</td>';
        }

        $html .= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html .= '</td>';

        $html .= '<td class="">';
        if ($element->getHint()) {
            $html .= '<div class="hint" >';
            $html .= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html .= '</div>';
        }
        $html .= '</td>';

        $html .= '</tr>';
        return $html;
    }

    /**
     * Fill in the field_name data that will be used in row template evaluation
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
       $row->addData(array('field_name' => $this->getFieldName(),
                           'group_name' => $this->getGroupName()));
    }
}