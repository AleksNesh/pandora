<?php
/**
 * Pan_JewelryDesigner Extension
 *
 * @category  Pan
 * @package   Pan_JewelryDesigner
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

/**
 * Designer Interface Block
 *
 * @category    Pan
 * @package     Pan_JewelryDesigner
 * @author      August Ash Team <core@augustash.com>
 */
class Pan_JewelryDesigner_Block_Designer extends Pan_JewelryDesigner_Block_Template
{
    /**
     * Set the block ID; used in HTML markup
     *
     * @param  string $id
     * @return Pan_JewelryDesigner_Block_Designer
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
}
