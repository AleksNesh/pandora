<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract Tab Block
 *
 * @category    Ash
 * @package     Ash_Devbar
 * @author      August Ash Team <core@augustash.com>
 */
abstract class Ash_Devbar_Block_Tab_Abstract extends Ash_Devbar_Block_Template
{
    /**
     * Set the block ID; used in HTML markup
     *
     * @param   string $id
     * @return  Ash_Devbar_Block_Tab_Abstract
     */
    public function setId($id)
    {
        $this->setData('block_id', $id);
        return $this;
    }

    /**
     * Retrieve the block ID; used in HTML markup
     *
     * @return  string
     */
    public function getId()
    {
        return $this->getData('block_id');
    }

    /**
     * Set tab label
     *
     * @param   string $label
     * @return  Ash_Devbar_Block_Tab_Abstract
     */
    public function setLabel($label)
    {
        $this->setData('label', $label);
        return $this;
    }

    /**
     * Retrieve tab label
     *
     * @return  string
     */
    public function getLabel()
    {
        return $this->getData('label');
    }

    /**
     * Set CSS classes
     *
     * @param   array $class
     * @return  Ash_Devbar_Block_Tab_Abstract
     */
    public function setCssClasses(array $classes)
    {
        $this->setData('css', $classes);
        return $this;
    }

    /**
     * Retrieve CSS classes
     *
     * @return  array
     */
    public function getCssClasses()
    {
        if ($this->getData('css')) {
            return array_values($this->getData('css'));
        }

        return array();
    }

    /**
     * Render label HTML
     *
     * @return  string
     */
    public function renderLabel()
    {
        $html  = '';
        $html .= $this->_beforeLabel();
        $html .= $this->_renderIcon();
        $html .= $this->getLabel();
        $html .= $this->_afterLabel();

        return $html;
    }

    /**
     * Before HTML render
     *
     * @return  string
     */
    protected function _beforeLabel()
    {
        $classes = array(
            'devbar-menu-item',
            // 'button',
        );

        // If a template is defined, then menu item is meant to expand. Make the
        // target directed at the appropriate ID
        if ($this->getTemplate()) {
            $target = '#devbar-' . $this->getId() . '-content';
        } else {
            $target = '#';
        }

        $html = sprintf('<a class="%s" href="%s">', implode(' ', $classes), $target);

        return $html;
    }

    /**
     * After HTML render
     *
     * @return  string
     */
    protected function _afterLabel()
    {
        return '</a>';
    }

    /**
     * Render Foundation icon HTML
     *
     * @return  string
     */
    protected function _renderIcon()
    {
        $html = '';

        if ($this->getData('icon')) {
            $html .= sprintf('<i class="devbar-icon fa %s"></i>', $this->getData('icon'));
        }

        return $html;
    }
}
