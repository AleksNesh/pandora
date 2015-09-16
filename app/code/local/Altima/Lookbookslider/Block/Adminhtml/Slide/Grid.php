<?php
/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://blog.altima.net.au/lookbook-magento-extension/lookbook-professional-licence/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 */
class Altima_Lookbookslider_Block_Adminhtml_Slide_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('slideGrid');
      $this->setDefaultSort('slide_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
                  
  }

  protected function _prepareCollection()
  {
      $slider_id = $this->getRequest()->getParam('slider_id');
      $collection = Mage::getModel('lookbookslider/slide')->getCollection()->addFieldToFilter('lookbookslider_id', $slider_id);
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('slide_id', array(
          'header'    => Mage::helper('lookbookslider')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'slide_id',
      ));
      
      $this->addColumn( 'image_path', array(
          'header' => Mage::helper( 'lookbookslider' )->__( 'Image' ), 
          'type' => 'image', 
          'width' => '75px', 
          'index' => 'image_path',
          'filter'    => false,
          'sortable'  => false,
          'renderer' => 'lookbookslider/adminhtml_template_grid_renderer_image',
      ));
      
      $this->addColumn('name', array(
          'header'    => Mage::helper('lookbookslider')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));

      $this->addColumn('position', array(
          'header'    => Mage::helper('lookbookslider')->__('Order'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'position',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('lookbookslider')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => Mage::helper('lookbookslider')->__('Enabled'),
              2 => Mage::helper('lookbookslider')->__('Disabled'),
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('lookbookslider')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('lookbookslider')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('lookbookslider')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('lookbookslider')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('slide_id');
        $this->getMassactionBlock()->setFormFieldName('slide');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('lookbookslider')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('lookbookslider')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('lookbookslider/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('lookbookslider')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('lookbookslider')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      $slider_id = $this->getRequest()->getParam('slider_id');
      return $this->getUrl('*/*/edit', array('id' => $row->getId(), 'slider_id'=>$slider_id));
  }

}