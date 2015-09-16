<?php
/**
 * Ash Up Extension
 *
 * Management interface for keeping Ash core extensions updated.
 *
 * @category    Ash
 * @package     Ash_Up
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extensions Grid Container
 *
 * @category    Ash_Up
 * @package     Ash_Up_Block
 */
class Ash_Up_Block_Adminhtml_Extension_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('extensionGrid');
        $this->setDefaultSort('extension_name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('extension_filter');
    }

    /**
     * Adds a button block to the layout for allowing users to check for
     * extension updates.
     *
     * @return Ash_Up_Block_Adminhtml_Extension_Grid
     */
    protected function _prepareLayout()
    {
        $this->setChild('check_updates_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                 ->setData(array(
                    'label'     => $this->__('Check for version updates'),
                    'onclick'   => "location.href = '{$this->getUrl('*/ashinstaller/checkupdates')}'",
                    'class'     => 'save',
                 ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Includes the custom check updates button in the main grid button area.
     *
     * @return string
     */
    public function getMainButtonsHtml()
    {
        return $this->getChildHtml('check_updates_button') . parent::getMainButtonsHtml();
    }

    /**
     * Instantiate and prepare collection
     *
     * @return  Ash_Up_Block_Adminhtml_Extension_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('ash_up/extension_collection');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return  Ash_Up_Block_Adminhtml_Extension_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('extension_name',
            array(
                'header'    => Mage::helper('ash_up')->__('Extension'),
                'index'     => 'extension_name',
        ));

        $this->addColumn('download_uri',
            array(
                'header'    => Mage::helper('ash_up')->__('Download URL'),
                'index'     => 'download_uri',
                'renderer'  => 'ash_up/adminhtml_extension_renderer_uri',
        ));

        $this->addColumn('last_downloaded',
            array(
                'header'    => Mage::helper('ash_up')->__('Last Downloaded'),
                'index'     => 'last_downloaded',
                'type'      => 'datetime',
                'width'     => 160,
                'default'   => '--',
        ));

        $this->addColumn('current_version',
            array(
                'header'    => Mage::helper('ash_up')->__('Installed'),
                'index'     => 'current_version',
                'width'     => 50,
                'renderer'  => 'ash_up/adminhtml_extension_renderer_version',
        ));

        $this->addColumn('last_checked',
            array(
                'header'    => Mage::helper('ash_up')->__('Last Checked'),
                'index'     => 'last_checked',
                'type'      => 'datetime',
                'width'     => 160,
                'default'   => '--',
        ));

        $this->addColumn('remote_version',
            array(
                'header'    => Mage::helper('ash_up')->__('Available'),
                'index'     => 'remote_version',
                'width'     => 50,
        ));

        // $this->addColumn('module_actions',
        //     array(
        //         'header'    => Mage::helper('cms')->__('Action'),
        //         'width'     => 70,
        //         'sortable'  => false,
        //         'filter'    => false,
        //         'renderer'  => 'ash_up/adminhtml_extension_renderer_action',
        // ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare mass action options for this grid
     *
     * @return  Ash_Up_Block_Adminhtml_Extension_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('extension_id');
        $this->getMassactionBlock()->setFormFieldName('extensions');

        $this->getMassactionBlock()->addItem('upgrade', array(
             'label'   => Mage::helper('ash_up')->__('Install/Upgrade'),
             'url'     => $this->getUrl('*/*/massUpgrade'),
             'confirm' => Mage::helper('ash_up')->__('Are you sure you want to install or upgrade these extensions?'),
        ));

        return $this;
    }

    /**
     * Define row click callback
     *
     * @return  string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
