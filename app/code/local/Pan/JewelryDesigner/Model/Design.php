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

class Pan_JewelryDesigner_Model_Design extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();

        // this refers to Pan_JewelryDesigner_Model_Resource_Design
        $this->_init('pan_jewelrydesigner/design');
    }

    public function loadDesign($designId, $customerId = null)
    {
        return $this->_loadDesign($designId, $customerId);
    }

    protected function _loadDesign($designId, $customerId = null)
    {
        $collection = $this->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('id', $designId);

        $inAdminArea = Mage::helper('pan_jewelrydesigner')->inAdminArea();
        if ($inAdminArea) {
            // allow admins to load any design...
        } else {
            // restrict scope of collection to designs created/cloned by the customer
            if (!empty($customerId)) {
                $collection->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('is_available', true);
            } else {
                // anonymous users (or those trying load inspiration designs)
                // should only be allowed to view inspiration designs
                $collection->addFieldToFilter('is_inspiration_design', true)
                    ->addFieldToFilter('is_available', true);
            }
        }

        // set limit to 1
        $collection->getSelect()->limit(1);

        // Mage::log($collection->getSelect()->__toString());

        $design = $collection->getFirstItem();

        return $design;
    }

    public function getDesigns($customerId = null, $includeInspirations = false)
    {
        return $this->_getDesigns($customerId, $includeInspirations);
    }

    protected function _getDesigns($customerId = null, $includeInspirations = false)
    {
        $collection = $this->getCollection()->addFieldToSelect('*');

        $inAdminArea = Mage::helper('pan_jewelrydesigner')->inAdminArea();

        if ($inAdminArea) {
            if ($includeInspirations) {
                $collection->addFieldToFilter('is_inspiration_design', true);
            } else {
                // allow admins to load any design?
                $collection->addFieldToFilter('admin_user_id', array('notnull' => 1));
            }
        } else {
            if (empty($customerId) && !$includeInspirations) {
                return array();
            } else {
                $collection->addFieldToFilter('is_available', true);

               // restrict scope of collection to designs created/cloned by the customer
                if (!empty($customerId)) {
                    $collection->addFieldToFilter('customer_id', $customerId);
                }

                if ($includeInspirations) {
                    $collection->addFieldToFilter('is_inspiration_design', true);
                }
            }
        }

        // Mage::log((string)$collection->getSelect());

        return $collection;
    }

    /**
     * Save the 'design' record (including JSON configuration object)
     *
     * @param  array    $postData
     * @return integer
     */
    public function saveDesign(array $postData)
    {
        $truthy     = array('1', 1, 'true', true, 'TRUE', TRUE, 'Y', 'y', 'YES', 'yes', 't', 'T');
        $falsey     = array('0', 0, 'false', false, 'FALSE', FALSE, 'N', 'n', 'NO', 'no', 'f', 'F');

        $products = $this->_decodeDesignConfiguration($postData['configuration']);

        if(array_key_exists('id', $postData) && !empty($postData['id'])) {
            $design = $this->load($postData['id']);
            if (!$design->getId()) {
                $design = Mage::getModel('pan_jewelrydesigner/design');
            }
        } else {
            $design = Mage::getModel('pan_jewelrydesigner/design');
        }

        foreach($postData as $attr => $value) {
            if ($attr === 'id') {
                continue;
            } else {
                $design->setData($attr, $value);
            }
        }

        $customer           = Mage::helper('pan_jewelrydesigner')->getCustomer();
        $customerId         = (!empty($customer)) ? $customer->getId() : null;

        $inAdminArea        = Mage::helper('pan_jewelrydesigner')->inAdminArea();

        if ($inAdminArea) {
            $adminUser      = Mage::getSingleton('admin/session')->getUser();
            $adminUserId    = ($adminUser) ? $adminUser->getData('user_id') : null;
        } else {
            $adminUserId    = null;
        }

        $jewelryType        = (array_key_exists('jewelry_type', $postData)) ? $postData['jewelry_type'] : 'bracelet';

        $inspirationValue   = (isset($postData['is_inspiration_design'])) ? $postData['is_inspiration_design'] : null;
        $isInspiration      = (isset($inspirationValue) && in_array($inspirationValue, $truthy, true)) ? true : false;

        $availableValue     = (isset($postData['is_available'])) ? $postData['is_available'] : null;
        $isAvailable        = (isset($availableValue) && in_array($availableValue, $truthy, true)) ? true : false;


        $design->setData('customer_id', $customerId);
        $design->setData('admin_user_id', $adminUserId);
        $design->setData('jewelry_type', $jewelryType);
        $design->setData('is_inspiration_design', $isInspiration);
        $design->setData('is_available', $isAvailable);

        $design->save();

        $designId = $design->getId();

        // save a screen shot of the designer workspace
        $this->_saveSnapshot($designId, $postData['base64Image']);

        // save the design's line items
        $this->_saveDesignItems($designId, $products);

        return $designId;
    }

    protected function _saveSnapshot($designId, $base64Image)
    {
        try {
            $design     = $this->load($designId);
            $designName = $design->getData('name');
            $helper     = Mage::helper('pan_jewelrydesigner');

            $snapshotDir            = $helper->getSnapshotDirectoryPath();
            $snapshotDirUrl         = $helper->getSnapshotDirectoryUrl();
            $image                  = str_replace('data:image/png;base64,', '', $base64Image);
            $decoded                = base64_decode($image);
            $fileName               = $helper->slugify($designName, '_') . '.png';
            $baseSnapshotDesignDir  = $snapshotDir . DIRECTORY_SEPARATOR . $designId;

            if(!is_dir($baseSnapshotDesignDir)) {
                mkdir($baseSnapshotDesignDir, 0777, true);
            }

            $filepath = $baseSnapshotDesignDir . DIRECTORY_SEPARATOR . $fileName;
            $fileUrl  = $snapshotDirUrl . DIRECTORY_SEPARATOR . $designId . DIRECTORY_SEPARATOR . $fileName;

            file_put_contents($filepath, $decoded);

            $design->setData('snapshot', $fileUrl);
            $design->save();
        } catch (Exception $e) {
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log('[CAUGHT EXCEPTION] : ' . $e->getMessage);
        }
    }

    protected function _getDesignItemsCollection($designId)
    {
        /**
         * Get the Design Items collection that belong to the specified Design
         */
        $collection = Mage::getModel('pan_jewelrydesigner/design_item')
            ->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('jewelery_design_id', $designId);

        return $collection;
    }

    /**
     * Save the line items that make up the design
     *
     * @param  integer|null $designId
     * @param  array        $products
     * @return void
     */
    protected function _saveDesignItems($designId, array $products)
    {
        /**
         * Get the Design Items collection that belong to the specified Design
         */
        $designItemsCollection = $this->_getDesignItemsCollection($designId);

        $existingDesignItemsProductIds  = $designItemsCollection->getColumnValues("product_id");

        $braceletProductIds = array();
        foreach ($products as $sku => $prod) {
            $braceletProductIds[$sku] = $prod['id'];
        }

        /**
         * Find out if any products in the $existingDesignItemsProductIds array
         * are no longer on the bracelet; if so remove these old line items from
         * the database.
         */
        $removeOldItems = array_diff($existingDesignItemsProductIds, $braceletProductIds);
        if (!empty($removeOldItems)) {
            foreach ($removeOldItems as $prodId) {
                $collection = Mage::getModel('pan_jewelrydesigner/design_item')
                    ->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('jewelery_design_id', $designId)
                    ->addFieldToFilter('product_id', $prodId);

                $oldItem = $collection->getFirstItem()->delete();
            }
        }

        /**
         * Create or update the design items
         */
        foreach ($products as $sku => $braceletProd) {
            if(in_array($braceletProd['id'], $existingDesignItemsProductIds)) {
                /**
                 * NOTE: I know this collection is repetitive but was the
                 * only way I could get the first item to return the correct
                 * database record with the correct ID value (it was always
                 * returning the very first ID value with the other data mixed
                 * in — so it was being a real PITA)
                 *
                 * LEAVE THIS AS IS!
                 */
                $collection = Mage::getModel('pan_jewelrydesigner/design_item')
                    ->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('jewelery_design_id', $designId)
                    ->addFieldToFilter('product_id', $braceletProd['id']);

                $item = $collection->getFirstItem();
            } else {
                // new "design item" (line item)
                $item = Mage::getModel('pan_jewelrydesigner/design_item');
            }

            $item->setData('jewelery_design_id', $designId);
            $item->setData('product_id', $braceletProd['id']);

            $prodType = strtolower(Mage::helper('ash_inflect')->singularize($braceletProd['type']));
            $prodType = ($prodType === 'spacer') ? 'charm' : $prodType;

            $item->setData('is_' . $prodType, true);

            $quantityOwned  = isset($braceletProd['quantity_owned']) ? $braceletProd['quantity_owned'] : 0;
            $quantity       = $braceletProd['qty'];
            $instances      = count($braceletProd['instances']);

            $item->setData('is_already_owned', $braceletProd['is_already_owned']);
            $item->setData('instances', $instances);
            $item->setData('quantity_owned', $quantityOwned);
            $item->setData('quantity', $quantity);
            $item->setData('unit_price', $braceletProd['base_price']);
            $item->setData('total_price', $braceletProd['line_item_price']);

            // save the line item
            $item->save();
        }

    }

    public function cloneDesign($designId, $customerId = null)
    {
        try {
            $inAdminArea = Mage::helper('pan_jewelrydesigner')->inAdminArea();
            if ($inAdminArea) {
                $adminUser      = Mage::getSingleton('admin/session')->getUser();
                $adminUserId    = ($adminUser) ? $adminUser->getData('user_id') : null;
            } else {
                $adminUserId    = null;
            }

            $origDesign = $this->load($designId);

            if($origDesign) {
                $copy = clone $origDesign;

                $copy->setData('id', null);
                $copy->setData('customer_id', $customerId);
                $copy->setData('admin_user_id', $adminUserId);
                $copy->setData('cloned_from_design_id', $designId);
                $copy->setData('is_inspiration_design', false);
                $copy->setData('times_cloned', 0);

                // allow the snapshot to be carried over
                // (just in case they don't edit the bracelet right away)
                // $copy->setData('snapshot', null);


                // Mage::log('$copy: ' . print_r($copy->getData(), true));

                $copy->save();

                // keep track how many times the original design has been cloned
                $origDesign->setData('times_cloned', $origDesign->getData('times_cloned') + 1);
                $origDesign->save();

                // clone the original design's line items
                $this->_cloneDesignItems($designId, $copy->getId());

                return $copy->getId();
            } else {
                throw new Exception("[RECORD NOT FOUND] Unable to clone design because it could not be found.");
            }
        } catch (Exception $e) {
            Mage::log('FROM ' .__CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    protected function _cloneDesignItems($origDesignId, $copyDesignId)
    {
        try {
            $origDesignItemsCollection = $this->_getDesignItemsCollection($origDesignId);
            foreach ($origDesignItemsCollection as $key => $origItem) {
                $itemCopy = clone $origItem;
                $itemCopy->setData('id', null);
                $itemCopy->setData('jewelery_design_id', $copyDesignId);
                $itemCopy->save();
            }
        } catch (Exception $e) {
            Mage::log('FROM ' .__CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log($e->getMessage());
        }
    }

    protected function _decodeDesignConfiguration($jsonString)
    {
        return Zend_Json::decode($jsonString);

    }


    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setData('created_at', $now);
        }
        $this->setData('updated_at', $now);
        return $this;
    }

    protected function _afterSave()
    {
        return parent::_afterSave();
    }
}
