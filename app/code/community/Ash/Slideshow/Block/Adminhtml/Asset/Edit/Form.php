<?php
/**
* Ash Slideshow Extension
*
* @category  Ash
* @package   Ash_Slideshow
* @copyright Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
* @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* @author    August Ash Team <core@augustash.com>
*
**/

class Ash_Slideshow_Block_Adminhtml_Asset_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'enctype' => 'multipart/form-data',
                'method'  => 'post',
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
