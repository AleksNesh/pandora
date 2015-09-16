<?php
/**
 * Altima Lookbook Free Extension
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
 * @category   Altima
 * @package    Altima_LookbookFree
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2012 Altima Web Systems (http://altimawebsystems.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Altima_Lookbook_Block_Adminhtml_Lookbook_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('lookbookGrid');
      $this->setDefaultSort('lookbook_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('lookbook/lookbook')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('lookbook_id', array(
          'header'    => Mage::helper('lookbook')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'lookbook_id',
      ));
      
      $this->addColumn( 'image', array(
          'header' => Mage::helper( 'lookbook' )->__( 'Image' ), 
          'type' => 'image', 
          'width' => '75px', 
          'index' => 'image',
          'filter'    => false,
          'sortable'  => false,
          'renderer' => 'lookbook/adminhtml_template_grid_renderer_image',
      ));
      
      $this->addColumn('name', array(
          'header'    => Mage::helper('lookbook')->__('Name'),
          'align'     =>'left',
          'index'     => 'name',
      ));

      $this->addColumn('position', array(
          'header'    => Mage::helper('lookbook')->__('Order'),
          'align'     =>'left',
          'width'     => '50px',
          'index'     => 'position',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('lookbook')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('lookbook')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('lookbook')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('lookbook')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('lookbook')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('lookbook_id');
        $this->getMassactionBlock()->setFormFieldName('lookbook');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('lookbook')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('lookbook')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('lookbook/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('lookbook')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('lookbook')->__('Status'),
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