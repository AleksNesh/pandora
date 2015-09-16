<?php
/**
 * Overrides for Pdflabel controller
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 * @copyright   Copyright (c) 2014 Alpine Consulting, Inc
 * @author      dmitry.ilin@alpineinc.com
 */

require_once 'Infomodus/Upslabel/controllers/Adminhtml/PdflabelsController.php';

/**
 * Class Alpine_PrintPdf_Adminhtml_PdflabelsController
 *
 * @category    Alpine
 * @package     Alpine_PrintPdf
 */
class Alpine_PrintPdf_Adminhtml_PdflabelsController extends Infomodus_Upslabel_Adminhtml_PdflabelsController
{

    /**
     * Label printing action
     */
    public function onepdfAction()
    {
        $orderId    = $this->getRequest()->getParam('order_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $type       = $this->getRequest()->getParam('type');
        $customSize = $this->getRequest()->getParam('custom_size');

        $imgPath = Mage::getBaseDir('media') . '/upslabel/label/';
        $message = '';

        $pdf = new Zend_Pdf();
        $i = 0;

        $collections = Mage::getModel('upslabel/upslabel');
        $colls = $collections->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('shipment_id', $shipmentId)
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('status', 0);

        $coll = 0;
        foreach ($colls as $value) {
            $coll = $value['upslabel_id'];
            break;
        }

        $width  = strlen(Mage::getStoreConfig('upslabel/printing/dimensionx')) > 0 ? Mage::getStoreConfig('upslabel/printing/dimensionx') : 1400 / 2.6;
        $height = strlen(Mage::getStoreConfig('upslabel/printing/dimensiony')) > 0 ? Mage::getStoreConfig('upslabel/printing/dimensiony') : 800 / 2.6;

        if (Mage::getStoreConfig('alpine_printpdf/qz/label_page_custom_size') && $customSize) {
            $holstSize = Mage::getStoreConfig('alpine_printpdf/qz/label_page_size_x') . ':' .
                         Mage::getStoreConfig('alpine_printpdf/qz/label_page_size_y') . ':';
        } elseif (
            strlen(Mage::getStoreConfig('upslabel/printing/holstx')) > 0 &&
            strlen(Mage::getStoreConfig('upslabel/printing/holsty')) > 0
        ) {
            $holstSize = Mage::getStoreConfig('upslabel/printing/holstx') . ':' .
                         Mage::getStoreConfig('upslabel/printing/holsty') . ':';
        } else {
            $holstSize = Zend_Pdf_Page::SIZE_A4;
        }

        $collectionOne = Mage::getModel('upslabel/upslabel')->load($coll);

        if ($collectionOne->getOrderId() == $orderId) {
            foreach ($colls as $collection) {
                if (
                    file_exists($imgPath . $collection->getLabelname()) &&
                    filesize($imgPath . $collection->getLabelname()) > 1024
                ) {
                    $page = $pdf->newPage($holstSize);
                    $pdf->pages[] = $page;

                    $fileContent = file_get_contents($imgPath . $collection->getLabelname());
                    $img = imagecreatefromstring($fileContent);

                    if (Mage::getStoreConfig('upslabel/printing/verticalprint') == 1) {
                        $fullImageWidth  = imagesx($img);
                        $fullImageHeight = imagesy($img);
                        imagecreatetruecolor($fullImageWidth, $fullImageHeight);

                        $col     = imagecolorallocate($img, 125, 174, 240);
                        $imgFull = imagerotate($img, -90, $col);
                    } else {
                        $imgFull = $img;
                    }

                    $rnd = rand(10000, 999999);
                    imagejpeg($imgFull, $imgPath . 'lbl' . $rnd . '.jpeg', 100);
                    try {
                        $image = Zend_Pdf_Image::imageWithPath($imgPath . 'lbl' . $rnd . '.jpeg');
                        $page->drawImage($image, 0, 0, $width, $height);
                        $i++;
                    } catch (Zend_Pdf_Exception $e) {
                        Mage::logException($e);
                        $message = $e->getMessage();
                    }
                    unlink($imgPath . 'lbl' . $rnd . '.jpeg');
                }
            }
        }

        if ($i > 0) {
            try {
                $pdfData = $pdf->render();
                $this->getResponse()->setHeader('Content-Disposition', 'inline; filename=result.pdf');
                $this->getResponse()->setHeader('Content-type', 'application/pdf');
                $this->getResponse()->setBody($pdfData);
            } catch (Zend_Pdf_Exception $e) {
                Mage::logException($e);
                $this->getResponse()->setBody($e->getMessage());
            }
        } else {
            $this->getResponse()->setBody($message);
        }
    }

}