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

class Ash_Slideshow_Block_Adminhtml_Slideshow_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * $_helper
     * @var Ash_Slideshow_Helper_Data
     */
    protected $_helper;

    public function __construct()
    {
        parent::__construct();

        $this->_helper = Mage::helper('ash_slideshow');

        // This is the primary key of the database
        $this->setId('slideshowGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ash_slideshow/slideshow')->getCollection();
        $collection->getSelect()->order('id ASC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'           => $this->_helper->__('ID'),
            'align'            =>'left',
            'index'            => 'id',
            'column_css_class' => 'js_slide_handle',
            'width'            => '0px',
            'sortable'         => false,
            'type'             => 'number'
        ));

        $this->addColumn('slideshow_name', array(
            'header'           => $this->_helper->__('Name'),
            'align'            =>'left',
            'index'            => 'slideshow_name',
            'column_css_class' => 'handle',
            'escape'           => true,
            'sortable'         => false,
        ));

        $this->addColumn('layout', array(
            'header'           => $this->_helper->__('Layout'),
            'align'            =>'left',
            'index'            => 'layout',
            'column_css_class' => 'handle',
            'escape'           => true,
            'sortable'         => false,
        ));

        $this->addColumn('status', array(
            'header'           => $this->_helper->__('Status'),
            'align'            =>'left',
            'index'            => 'status',
            'column_css_class' => 'handle',
            'escape'           => true,
            'sortable'         => false,
            'type'             => 'options',
            'options'          => array(
                1 => $this->_helper->__('Enabled'),
                0 => $this->_helper->__('Disabled'),
            )
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
