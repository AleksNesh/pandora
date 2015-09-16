<?php

class Amasty_Shiprestriction_Block_Adminhtml_Rule_Edit_Tab_Conditions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('salesrule')->__('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('salesrule')->__('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('amshiprestriction_rule');

        $form = new Varien_Data_Form();
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/adminhtml_rule/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('rule_conditions_fieldset', array(
            'legend'=>Mage::helper('salesrule')->__('Apply the rule only if the following conditions are met (leave blank for all products)')
        ))->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('salesrule')->__('Conditions'),
            'title' => Mage::helper('salesrule')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));
        
        $hlp = Mage::helper('amshiprestriction');
        $fldAdv = $form->addFieldset('advanced', array('legend'=> $hlp->__('Advanced'))); 
        $fldAdv->addField('out_of_stock', 'select', array(
            'label'     => $hlp->__('Apply for backorders only'),
            'name'      => 'out_of_stock',
            'options'   => array(Mage::helper('catalog')->__('No'), Mage::helper('catalog')->__('Yes')),
        ));        
        

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
