<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_Backend_Pdftotal extends Mage_Core_Model_Config_Data
{
    protected $_eventPrefix = 'pdfcustomiser_pdftotal';

    /**
     * if we dont' have a value yet retrieve a starting value from
     * the default config xml configuration
     *
     * @return Fooman_PdfCustomiser_Model_Backend_Pdftotal
     */
    protected function _afterLoad()
    {
        if ($this->getValue() == '') {
            $this->setValue((string)Mage::getConfig()->getNode($this->_getGlobalConfigPath()));
        }
        return parent::_afterLoad();
    }

    /**
     * get the corresponding sort order config path
     *
     * @return string
     */
    protected function _getRealConfigPath()
    {
        return substr($this->getPath(), 6) . '/sort_order';
    }

    /**
     * convert given config path to a global node
     * @return string
     */
    protected function _getGlobalConfigPath()
    {
        return 'global/' . $this->_getRealConfigPath();
    }
}