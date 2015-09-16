<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_PdfCustomiser
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Fooman_PdfCustomiser_Model_System_Stores
{
    /**
     * supply dropdown choices for alternative stores
     *
     * @return array 
     */
    public function toOptionArray()
    {
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $optGroupLabel = $group->getName();
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $optGroupValue[] = array('label' => $store->getName(), 'value' => $store->getStoreId());
                }
                $optGroups[] = array('label' => $optGroupLabel, 'value' => $optGroupValue);
                unset($optGroupValue, $optGroup);
            }
        }
        array_unshift($optGroups, array('label' => 'Disabled', 'value' => ''));
        return $optGroups;
    }
}
