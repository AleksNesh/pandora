<?php
/**
 * Magento Developer's Toolbar
 *
 * @category    Ash
 * @package     Ash_Bar
 * @copyright   Copyright (c) 2012 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Toolbar Tab Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Tab extends Ash_Bar_Block_Template
{
    /**
     * Set the block ID; used in HTML markup
     *
     * @param  string $id
     * @return Ash_Bar_Block_Toolbar_Tab
     */
    public function setId($id)
    {
        $this->setData('block_id', $id);
        return $this;
    }

    /**
     * Retrieve the block ID; used in HTML markup
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData('block_id');
    }

    /**
     * Render block's content to HTML
     *
     * @return string
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
     * Render Icon
     *
     * @return string
     */
    protected function _renderIcon()
    {
        $html = '';
        if ($this->getIcon()) {
            $html .= '<div class="ashbar-icon"><i class="' . $this->getIcon()
                . '"></i></div>';
        }

        return $html;
    }

    /**
     * Before HTML render
     *
     * @return string
     */
    protected function _beforeLabel()
    {
        $html  = '';

        if ($this->getTemplate()) {
            $html .= '<a href="#ashbar-' . $this->getId() . '-content">';
        }

        $html .= '<button class="btn btn-small" type="button">';

        return $html;
    }

    /**
     * After HTML render
     *
     * @return string
     */
    protected function _afterLabel()
    {
        $html  = '</button>';

        if ($this->getTemplate()) {
            $html .= '</a>';
        }

        return $html;
    }
}
