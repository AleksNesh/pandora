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

class Ash_Slideshow_Block_Adminhtml_Slideshow_New_Tab_Assets extends Mage_Adminhtml_Block_Widget_Grid
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

        $this->setRowClickCallback();
        // This is the primary key of the database
        $this->setId('slideshowAssetsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }


    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'ash_slideshow/asset';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel($this->_getCollectionClass())->getCollection();

        $collection->getSelect()->order('id ASC');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('asset_id', array(
            'header'           => $this->_helper->__('ID'),
            'align'            => 'left',
            'index'            => 'id',
            'column_css_class' => 'handle',
            'width'            => '0px',
            'type'             => 'checkbox',
            'field_name'       => 'assets[]',
            'values'           => 'slide_custom_check', // handeled by custom renderer to show slide selected assets
            'escape'           => true,
            'sortable'         => false,
            'renderer'         => new Ash_Slideshow_Block_Adminhtml_Widget_Grid_Column_Renderer_Checkbox(),
        ));


        $this->addColumn('asset_image', array( // <- Field name
            'header'    => $this->_helper->__('Image'),
            'align'     => 'left',
            'width'     => '100px',
            'index'     => 'image', // <- Table Name
            'type'      => 'image', // <- Type of field
            'escape'    => true,
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => new Ash_Slideshow_Block_Adminhtml_Grid_Renderer_Image(),
        ));

        $this->addColumn('asset_title', array(
            'header'    => $this->_helper->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
            'sortable'  => false,
        ));


        $this->addColumn('asset_link_url', array(
            'header'    => $this->_helper->__('URL'),
            'align'     => 'left',
            'index'     => 'link_url',
            'sortable'  => false,
        ));

        $this->addColumn('updated_at', array(
            'header'    => $this->_helper->__('Updated At'),
            'align'     => 'left',
            'index'     => 'updated_at',
            'width'     => '120px',
            'type'      => 'datetime',
            'sortable'  => false,
        ));

        $this->addColumn('asset_status', array(
            'header'    => $this->_helper->__('Status'),
            'index'     => 'status',
            'width'     => '100px',
            'type'      => 'options',
            'options'   => array(
                '1' => $this->_helper->__('Enabled'),
                '0' => $this->_helper->__('Disabled'),
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
