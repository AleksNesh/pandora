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
class Altima_Lookbookslider_Block_Adminhtml_Lookbookslider_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('lookbooksliderGrid');
      $this->setDefaultSort('lookbookslider_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('lookbookslider/lookbookslider')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('lookbookslider_id', array(
          'header'    => Mage::helper('lookbookslider')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'lookbookslider_id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('lookbookslider')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));
      
      $this->addColumn( 'dimensions', array(
          'header' => Mage::helper( 'lookbookslider' )->__( 'Slider Size' ), 
          'width' => '130px', 
          'index' => 'dimensions',
          'filter'    => false,
          'sortable'  => false,
          'renderer' => 'lookbookslider/adminhtml_template_grid_renderer_dimensions',
      ));

      $this->addColumn('pages', array(
          'header' => Mage::helper( 'lookbookslider' )->__( 'CMS Pages' ), 
          'width' => '200px', 
          'index' => 'pages',
          'filter'    => false,
          'sortable'  => false,
          'renderer' => 'lookbookslider/adminhtml_template_grid_renderer_pages',
      ));
 
       $this->addColumn('categories', array(
          'header' => Mage::helper( 'lookbookslider' )->__( 'Categories' ),  
          'index' => 'categories',
          'filter'    => false,
          'sortable'  => false,
          'renderer' => 'lookbookslider/adminhtml_template_grid_renderer_categories',
      ));
                 
      $this->addColumn('position', array(
          'header'    => Mage::helper('lookbookslider')->__('Position'),
          'align'     =>'left',
          'width' => '130px',
          'index'     => 'position',
          'type'      => 'options',
          'options'   => Mage::getSingleton('lookbookslider/position')->toGridOptionArray(),
      ));
      
      $this->addColumn('status', array(
          'header'    => Mage::helper('lookbookslider')->__('Status'),
          'align'     => 'left',
          'width'     => '90px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => Mage::helper('lookbookslider')->__('Enabled'),
              2 => Mage::helper('lookbookslider')->__('Disabled'),
          ),
      ));
	  
        $this->addColumn('action_edit',
            array(
                'header'    =>  '',
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('lookbookslider')->__('Edit Slider'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
       $this->addColumn('action_manage_slides',
            array(
                'header'    =>  '',
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('lookbookslider')->__('Manage Slides'),
                        'url'       => array('base'=> 'lookbookslider/adminhtml_slide/index'),
                        'field'     => 'slider_id'
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
        $this->setMassactionIdField('lookbookslider_id');
        $this->getMassactionBlock()->setFormFieldName('lookbookslider');

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
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}