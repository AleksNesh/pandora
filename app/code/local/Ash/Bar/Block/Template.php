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
 * Abstract Block
 *
 * @category    Ash
 * @package     Ash_Bar
 * @author      August Ash Team <core@augustash.com>
 */
class Ash_Bar_Block_Template extends Mage_Core_Block_Template
{
    /**
     * Retrieve block view from file (template)
     *
     * @param   string $fileName
     * @return  string
     */
    public function fetchView($fileName)
    {
        $module_dir = Mage::getModuleDir('', 'Ash_Bar');
        $this->setScriptPath($module_dir . '/Template');
        return parent::fetchView($this->getTemplate());
    }

    /**
     * Set template location directory
     *
     * @param   string $dir
     * @return  Ash_Bar_Block_Abstract
     */
    public function setScriptPath($dir)
    {
        $scriptPath = realpath($dir);
        if ($this->_checkValidScriptPath($scriptPath)) {
            $this->_viewDir = $dir;
        } else {
            Mage::logException('Not valid script path:' . $dir, Zend_Log::CRIT, null, null, true);
        }
        return $this;
    }

    /**
     * Get custom skin directory URL
     *
     * @return  string
     */
    public function getSkinPath()
    {
        $baseSkin = Mage::getBaseUrl('skin');
        $baseSkin = preg_replace('{/$}', '', $baseSkin);
        $url      = str_replace('{{base_skin}}', $baseSkin,
            '{{base_skin}}/frontend/ash_bar');

        if ((strpos($url, $baseSkin) !== 0) && $url[0] != '/' ) {
            $url = '/' . $url;
        }

        return $url;
    }

    /**
     * Validate script path
     *
     * @param   string $scriptPath
     * @return  boolean
     */
    protected function _checkValidScriptPath($scriptPath)
    {
        $paths = array(Mage::getBaseDir('design'), Mage::getModuleDir('', 'Ash_Bar'));
        $valid = false;
        foreach($paths as $path) {
            if(strpos($scriptPath, realpath($path)) === 0) {
                $valid = true;
            }
        }
        return $valid;
    }
}
