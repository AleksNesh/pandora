<?php
class Magestore_Giftwrap_Block_Adminhtml_Giftwrap_Edit extends Mage_Adminhtml_Block_Widget
{

	protected $_editMode = false;
		
	public function __construct() {		
		parent::__construct();
		
    $this->setTemplate('giftwrap/edit.phtml');
	}
	
	public function getModel()
	{
			return Mage::registry('giftwrap_data');
	}
	
	protected function _prepareLayout()
	{
			$this->setChild('back_button',
					$this->getLayout()->createBlock('adminhtml/widget_button')
							->setData(array(
									'label'     => Mage::helper('giftwrap')->__('Back'),
									'onclick'   => "window.location.href = '" . $this->getUrl('*/*') . "'",
									'class'     => 'back'
							))
			);

			$this->setChild('reset_button',
					$this->getLayout()->createBlock('adminhtml/widget_button')
							->setData(array(
									'label'     => Mage::helper('giftwrap')->__('Reset'),
									'onclick'   => 'window.location.href = window.location.href'
							))
			);

			$this->setChild('save_button',
					$this->getLayout()->createBlock('adminhtml/widget_button')
							->setData(array(
									'label'     => Mage::helper('giftwrap')->__('Save'),
									'onclick'   => 'giftwrapControl.save()',
									'class'     => 'save'
							))
			);
			
			
			$this->setChild('save_continue_button',
					$this->getLayout()->createBlock('adminhtml/widget_button')
							->setData(array(
									'label'     => Mage::helper('giftwrap')->__('Save And Continue Edit'),
									'onclick'   => 'giftwrapControl.save_continue()',
									'class'     => 'save'
							))
			);

			$this->setChild('delete_button',
					$this->getLayout()->createBlock('adminhtml/widget_button')
							->setData(array(
									'label'     => Mage::helper('giftwrap')->__('Delete'),
									'onclick'   => 'giftwrapControl.deleteGiftwrap()',
									'class'     => 'delete'
							))
			);

			return parent::_prepareLayout();
	}
	
	public function getBackButtonHtml()
	{
		return $this->getChildHtml('back_button');
	}
		
	public function getResetButtonHtml()
	{
			return $this->getChildHtml('reset_button');
	}
		
	public function getSaveButtonHtml()
	{
			return $this->getChildHtml('save_button');
	}
	
	public function getSaveContinueButtonHtml()
	{
			return $this->getChildHtml('save_continue_button');
	}
		
	public function getDeleteButtonHtml()
	{
			return $this->getChildHtml('delete_button');
	}
		
	public function getForm()
	{
			return $this->getLayout()
					->createBlock('giftwrap/adminhtml_giftwrap_edit_form')
					->toHtml();
	}
	
	public function setEditMode($value = true)
	{
			$this->_editMode = (bool)$value;
			return $this;
	}
	
	public function getEditMode()
	{
			return $this->_editMode;
	}

	public function getHeaderText()
	{
			if ($this->getEditMode()) {
					return Mage::helper('giftwrap')->__('Edit Gift Box');
			}

			return  Mage::helper('giftwrap')->__('New Gift Box');
	}

	public function getSaveUrl()
	{
		$store_id = $this->getRequest()->getParam('store',0);
		return $this->getUrl('*/*/save',array('store'=>$store_id));
	}
		
	public function getDeleteUrl()
	{
			return $this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('id')));
	}
		
		
}