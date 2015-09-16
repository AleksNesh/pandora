<?php
/**
 * Ash Slideshow Extension
 *
 * @category  Ash
 * @package   Ash_Slideshow
 * @copyright Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author    August Ash Team <core@augustash.com>
 */

class Ash_Slideshow_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getAssetUrl(Ash_Slideshow_Model_Asset $asset)
    {
        $mediaBaseUrl = $this->getMediaBaseUrl();
        return $mediaBaseUrl . $asset->getData('image');
    }

    public function getMediaBaseUrl()
    {
        return $this->getBaseUrlFor('media');
    }

    public function getBaseUrlFor($urlType = 'media')
    {
        switch ($urlType) {
            case 'link':
                $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                break;
            case 'direct_link':
                $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK);
                break;
            case 'web':
                $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
                break;
            case 'skin':
                $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
                break;
            case 'js':
                $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS);
                break;
            case 'media':
            default:
                $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
                break;
        }

        return $baseUrl;
    }

    /**
     * Get the file extension from a string
     *
     * @param    string  $file_name
     * @return   string
     */
    public function getFileExtension($file_name)
    {
        return '.' . pathinfo($file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get the file name from a string
     *
     * @param    string  $file_name
     * @return   string
     */
    public function getFileName($file_name)
    {
        return pathinfo($file_name, PATHINFO_FILENAME);
    }

    /**
     * Maximum size of uploaded files.
     *
     * @return int
     */
    public function getMaxUploadSize()
    {
        return min(ini_get('post_max_size'), ini_get('upload_max_filesize'));
    }

    /**
     * Remove Image from disk.
     *
     * @return void
     */
    public function deleteImage($image_path)
    {
        if(file_exists($path = Mage::getBaseDir('media') . DS . $image_path)) {
            try {
                unlink(Mage::getBaseDir('media') . DS . $image_path);
            } catch (Exception $e) {
                Mage::log("FROM CLASS " . __CLASS__ . ' IN FILE ' . __FILE__ . ' AT LINE ' . __LINE__);
                Mage::log($e->getMessage());
            }
        }
    }

    /**
     * tileImageUpload custom method to upload
     * a tile image
     *
     * @param  Varien_File_Uploader $uploader Magento file uploader class
     * @param  Array                $options  Options/Image Info
     * @return String                         Return the path where the image was saved
     *
     *
     */
    public function assetImageUpload(Varien_File_Uploader $uploader, Array $options)
    {
        $requirements = array('media_absolute_path', 'file_info', 'random_string', 'media_url', 'field_slug');

        // Check if options have all required keys
        // return false otherwise
        if( !$this->required($requirements, $options)) return false;

        // Reassign variable for better reading
        $fileName = $options['file_info'][$options['field_slug']]['name'];

        // Sets the image types accepted and uploader settings
        $imageTypes = array('jpg','jpeg','gif','png');
        if(isset($options['image_types']) and !empty($options['image_types'])) {
            $imageTypes = $options['image_types'];
        }

        $uploader->setAllowedExtensions($imageTypes);
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);

        // Parse/Sanitaze the image name and path
        $image_path = $options['media_absolute_path'] . DS .$options['media_url'];
        $image_name = Mage::helper('ash_slideshow/inflector')->slugify($this->getFileName($fileName));
        $image_ext  = $this->getFileExtension($fileName);
        $image_name = $options['random_string'] . '__' . $image_name . $image_ext;


        $uploader->save($image_path, $image_name);

        return $image_name;
    }

    /**
    * required method returns false if the $data array does not contain all
    * of the keys assigned by the $required array.
    *
    *
    * <code>
    *     ..code..
    *     public function getMuchacho($options)
    *     {
    *         $requirements = array('taco', 'tortia');
    *
    *         if( !$this->required($requirements, $options)) return false;
    *
    *         #.. required passess continue code ..#
    *     }
    *     ..code..
    * </code>
    *
    * @param array $required
    * @param array $data
    * @return bool
    */
    function required($required, $data)
    {
        foreach($required as $field) if( !isset($data[$field])) return false;
        return true;
    }
}
