<?php

class Magestore_Giftwrap_Block_Adminhtml_Giftwrap_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Define Form settings
     *
     */
    var $_checkeds = array();
    var $_disableds = array();

    public function __construct() {
        parent::__construct();
    }

    /**
     * Retrieve template object
     *
     * @return Mage_Newsletter_Model_Template
     */
    public function getModel() {
        return Mage::registry('giftwrap_data');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm() {

        $giftwrap_data = array();
        $model = $this->getModel();
        if (Mage::getSingleton('adminhtml/session')->getGiftwrapData()) {
            $giftwrap_data = Mage::getSingleton('adminhtml/session')->getGiftwrapData();
        } elseif (Mage::registry('giftwrap_data')) {
            $giftwrap_data = $model->getData();
        }


        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $store_id = $this->getRequest()->getParam('store', 0);

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('giftwrap')->__('Gift Box Information')));
        // 'class'     => 'fieldset-wide'
        // ));
        $this->setStatus($giftwrap_data);
        //var_dump($this->_checkeds);die();
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
                'value' => $model->getId(),
            ));
        }

        $fieldset->addField('title', 'text', array(
            'name' => 'title',
            'label' => Mage::helper('giftwrap')->__('Gift Box Title'),
            'title' => Mage::helper('giftwrap')->__('Gift Box Title'),
            'required' => true,
            'value' => $model->getTitle(),
            'disabled' => $this->_disableds['title'],
        ));

        if ($store_id != 0)
            $fieldset->addField('label_default_title', 'checkbox', array(
                'label' => Mage::helper('giftwrap')->__('User Default Style Title'),
                'name' => 'label_default_title',
                'checked' => $this->_checkeds['title'],
                'onclick' => 'doCheck(\'label_default_title\',\'default_title\',\'title\')',
            ));

        $fieldset->addField('default_title', 'hidden', array(
            'name' => 'default_title',
            'value' => $model->getDefaultTitle(),
        ));

        $fieldset->addField('price', 'text', array(
            'name' => 'price',
            'label' => Mage::helper('giftwrap')->__('Price'),
            'title' => Mage::helper('giftwrap')->__('Price'),
            'required' => true,
            'class' => 'required-entry validate-zero-or-greater input-text',
            'value' => $model->getPrice(),
            'disabled' => $this->_disableds['price'],
            'note' => 'Cost per box/items -> <a href="' . $this->getUrl('adminhtml/system_config/edit/section/giftwrap') . '" >settings</a>'
        ));

        if ($store_id != 0)
            $fieldset->addField('label_default_price', 'checkbox', array(
                'label' => Mage::helper('giftwrap')->__('User Default Price'),
                'name' => 'label_default_price',
                'checked' => $this->_checkeds['price'],
                'onclick' => 'doCheck(\'label_default_price\',\'default_price\',\'price\')',
            ));

        $fieldset->addField('default_price', 'hidden', array(
            'name' => 'default_price',
            'value' => $model->getDefaultPrice(),
        ));

        $imagePath = '';
        if ($model->getImage()) {
            $imagePath = 'giftwrap/' . $model->getImage();
        }
        $fieldset->addField('image', 'image', array(
            'name' => 'image',
            'label' => Mage::helper('giftwrap')->__('Gift Box Image'),
            'title' => Mage::helper('giftwrap')->__('Gift Box Image'),
            'required' => false,
            'value' => $imagePath,
            'disabled' => $this->_disableds['image'],
            'note' => '(jpeg, tiff, png formats supported)'
        ));

        if ($store_id != 0)
            $fieldset->addField('label_default_image', 'checkbox', array(
                'label' => Mage::helper('giftwrap')->__('User Default Style Image'),
                'name' => 'label_default_image',
                'checked' => $this->_checkeds['image'],
                'onclick' => 'doCheck(\'label_default_image\',\'default_image\',\'image\')',
                'after_element_html' => '<small>(jpeg, tiff, png)</small>',
            ));

        $fieldset->addField('default_image', 'hidden', array(
            'name' => 'default_image',
            'value' => $model->getDefaultImage(),
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('cms')->__('Status'),
            'title' => Mage::helper('cms')->__('Status'),
            'name' => 'status',
            'required' => false,
            'options' => array(
                '1' => Mage::helper('giftwrap')->__('Enabled'),
                '2' => Mage::helper('giftwrap')->__('Disabled'),
            ),
            'disabled' => $this->_disableds['status'],
            'value' => $model->getStatus()
        ));

        if ($store_id != 0)
            $fieldset->addField('label_default_status', 'checkbox', array(
                'label' => Mage::helper('giftwrap')->__('User Default Status'),
                'name' => 'label_default_status',
                'checked' => $this->_checkeds['status'],
                'onclick' => 'doCheck(\'label_default_status\',\'default_status\',\'status\')',
            ));

        $fieldset->addField('default_status', 'hidden', array(
            'name' => 'default_status',
            'value' => $model->getDefaultStatus(),
        ));


        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    function setStatus($data) {
        $arrFielName = array(0 => 'title', 1 => 'price', 2 => 'character', 3 => 'image', 4 => 'personal_message', 5 => 'status', 6 => 'sort_order',);
        foreach ($arrFielName as $fielName) {
            // var_dump($data['default_'. $fielName]);
            // var_dump($data['store_id']);
            // die();
            if (isset($data['default_' . $fielName]) && $data['default_' . $fielName] && $data['store_id']) {
                $this->_checkeds[$fielName] = 'checked';
                $this->_disableds[$fielName] = true;
            } else {
                $this->_checkeds[$fielName] = '';
                $this->_disableds[$fielName] = false;
            }
        }
    }

}
