<?php

/**
 * Simple module for extending core EM_Megamenupro module
 *
 * @category    Pan
 * @package     Pan_Megamenupro
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @author      Josh Johnson (August Ash)
 */
class Pan_Megamenupro_Block_Adminhtml_Menueditor_Edit extends EM_Megamenupro_Block_Adminhtml_Menueditor_Edit
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('pan_megamenupro/edit.phtml');
    }
}
