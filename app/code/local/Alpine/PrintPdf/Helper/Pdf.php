<?php
/**
 * PDF File generation helper
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

/**
 * Class Alpine_PrintPdf_Helper_Pdf
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Helper_Pdf extends Mage_Core_Helper_Abstract
{

    const PDF_DIRECTORY = 'pdf';

    /**
     * @return string
     * @throws Exception
     */
    public function getPdfDirectory()
    {
        $directory = Mage::getBaseDir('var') . DS . self::PDF_DIRECTORY;
        $varienFile = new Varien_Io_File();
        $varienFile->checkAndCreateFolder($directory);

        return $directory;
    }


    /**
     * @param $file
     * @param $filename
     * @throws Exception
     */
    public function saveFile($file, $filename)
    {
        $varienFile = new Varien_Io_File();
        $varienFile->open();
        $path = $this->getFilePath($filename);
        $varienFile->checkAndCreateFolder($path);

        if ($varienFile->write($path . DS . $filename, $file) !== false) {
            return $path . DS . $filename;
        } else {
            throw new Exception('Could not save PDF file');
        }
    }

    /**
     * @param $filename
     * @return string
     */
    public function getFilePath($filename)
    {
        return $this->getPdfDirectory() . DS . Mage_Core_Model_File_Uploader::getDispretionPath($filename);
    }

    /**
     * @param $data
     * @return string
     */
    public function generateFileName($data)
    {
        return sha1($data) . '.pdf';
    }

    /**
     * @param $filename
     * @return bool|string
     */
    public function loadFile($filename)
    {
        $varienFile = new Varien_Io_File();
        $varienFile->open();
        $path = $this->getFilePath($filename);
        return $varienFile->read($path . DS . $filename);
    }

}