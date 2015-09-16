<?php

/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

class Infomodus_Upslabel_Adminhtml_PdflabelsController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $ptype = $this->getRequest()->getParam('type');
        if ($ptype != 'lists') {
            $type = 'shipment';
            $order_ids = $this->getRequest()->getParam($ptype . '_ids');
            if ($ptype == 'creditmemo') {
                $ptype = 'shipment';
                $type = 'refund';
            }
            $resp = $this->create($order_ids, $type, $ptype);
        } else {
            $order_ids = $this->getRequest()->getParam('upslabel');
            $resp = $this->createFromLists($order_ids);
        }

        if (!$resp) {
            $this->_redirectReferer();
        }
    }

    public function onepdfAction()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $shipment_id = $this->getRequest()->getParam('shipment_id');
        $type = $this->getRequest()->getParam('type');
        $img_path = Mage::getBaseDir('media') . '/upslabel/label/';
        $url_image_path = Mage::getBaseUrl('media') . 'upslabel/label/';
        $pdf = new Zend_Pdf();
        $i = 0;
        $collections = Mage::getModel('upslabel/upslabel');
        $colls = $collections->getCollection()->addFieldToFilter('order_id', $order_id)->addFieldToFilter('shipment_id', $shipment_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
        foreach ($colls AS $k => $v) {
            $coll = $v['upslabel_id'];
            break;
        }
        $collection_one = Mage::getModel('upslabel/upslabel')->load($coll);
        

        if ($collection_one->getOrderId() == $order_id) {
            foreach ($colls AS $collection) {
                if (file_exists($img_path . $collection->getLabelname()) && filesize($img_path . $collection->getLabelname()) > 512) {
                    if ($collection->getTypePrint() == "GIF") {
                        $pdf->pages[] = self::_setLabelToPage($img_path . $collection->getLabelname());
                        $collection->setRvaPrinted(1);
                        $collection->save();
                        $i++;
                    }
                }
            }
        }
        if ($i > 0) {
            $pdfData = $pdf->render();
            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf");
            echo $pdfData;
        }
    }

    static public function create($order_ids, $type, $ptype)
    {
        $img_path = Mage::getBaseDir('media') . '/upslabel/label/';
        $pdf = new Zend_Pdf();
        $i = 0;
        //$pdf->pages = array_reverse($pdf->pages);
        if (!is_array($order_ids)) {
            $order_ids = explode(',', $order_ids);
        }

        $configModuleNode = Mage::getConfig()->getNode('default/upslabel/myoption/multistore/active');
        $arrZPL = array();
        foreach ($order_ids as $order_id) {
            

            $collections = Mage::getModel('upslabel/upslabel');
            $colls = $collections->getCollection()->addFieldToFilter($ptype . '_id', $order_id)->addFieldToFilter('type', $type)->addFieldToFilter('status', 0);
            if (Mage::getStoreConfig('upslabel/printing/bulk_printing_all') == 1) {
                $colls->addFieldToFilter('rva_printed', 0);
            }
            if ($colls) {
                foreach ($colls AS $k => $v) {
                    $coll = $v['upslabel_id'];
                    $collection = Mage::getModel('upslabel/upslabel')->load($coll);
                    if (($collection->getOrderId() == $order_id && $ptype == "order") || ($collection->getShipmentId() == $order_id && $ptype != "order")) {
                        if (file_exists($img_path . $collection->getLabelname()) && filesize($img_path . $collection->getLabelname()) > 1024) {
                            if ($collection->getTypePrint() == "GIF") {
                                $pdf->pages[] = self::_setLabelToPage($img_path . $collection->getLabelname());
                                $i++;
                            } else {
                                $ip = trim(Mage::getStoreConfig('upslabel/printing/automatic_printing_ip'));
                                $port = trim(Mage::getStoreConfig('upslabel/printing/automatic_printing_port'));
                                if (strlen($ip) > 0 && strlen($port) > 0 && Mage::getStoreConfig('upslabel/printing/printer') != 'GIF') {
                                    $data = file_get_contents($img_path . $collection->getLabelname());
                                    Mage::helper('upslabel/help')->sendPrint($data);
                                } else {
                                    $arrZPL[] = array('localname' => $collection->getLabelname(), 'name' => $img_path . $collection->getLabelname());
                                }
                            }
                            $collection->setRvaPrinted(1);
                            $collection->save();
                        }
                    }
                }
            }
        }
        //$pdf->save();
        if (count($arrZPL) > 0) {
            $zip = new ZipArchive();
            $zip_name = sys_get_temp_dir() . DS . 'labels' . time() . uniqid() . '.zip';
            if ($zip->open($zip_name, ZIPARCHIVE::CREATE) !== TRUE) {
            }
            foreach ($arrZPL AS $coll) {
                if (file_exists($coll['name'])) {
                    $zip->addFile($coll['name'], $coll['localname']);
                }
            }
            if ($i > 0) {
                $pdfData = $pdf->render();
                $zip->addFromString('pdf_labels_only.pdf', $pdfData);
            }
            $zip->close();
            if (file_exists($zip_name)) {
                header('Content-type: application/zip');
                header('Content-Disposition: attachment; filename="ups_shipping_labels.zip"');
                readfile($zip_name);
                unlink($zip_name);
            }
        } else if ($i > 0) {
            $pdfData = $pdf->render();
            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf");
            echo $pdfData;
            return true;
        } else {
            return false;
        }
    }

    static public function createFromLists($order_ids)
    {
        $img_path = Mage::getBaseDir('media') . '/upslabel/label/';
        $pdf = new Zend_Pdf();
        $i = 0;
        if (!is_array($order_ids)) {
            $order_ids = explode(',', $order_ids);
        }
        $configModuleNode = Mage::getConfig()->getNode('default/upslabel/myoption/multistore/active');
        $arrZPL = array();
        foreach ($order_ids as $order_id) {
            

            $collection = Mage::getModel('upslabel/upslabel')->load($order_id);
            if ($collection && $collection->getStatus() == 0) {
                if (file_exists($img_path . $collection->getLabelname()) && filesize($img_path . $collection->getLabelname()) > 1024) {
                    if ($collection->getTypePrint() == "GIF") {
                        $pdf->pages[] = self::_setLabelToPage($img_path . $collection->getLabelname());
                        $i++;
                    } else {
                        $ip = trim(Mage::getStoreConfig('upslabel/printing/automatic_printing_ip'));
                        $port = trim(Mage::getStoreConfig('upslabel/printing/automatic_printing_port'));
                        if (strlen($ip) > 0 && strlen($port) > 0 && Mage::getStoreConfig('upslabel/printing/printer') != 'GIF') {
                            $data = file_get_contents($img_path . $collection->getLabelname());
                            Mage::helper('upslabel/help')->sendPrint($data);
                        } else {
                            $arrZPL[] = array('localname' => $collection->getLabelname(), 'name' => $img_path . $collection->getLabelname());
                        }
                    }
                    $collection->setRvaPrinted(1);
                    $collection->save();
                }
            }
        }
        //$pdf->save();
        if (count($arrZPL) > 0) {
            $zip = new ZipArchive();
            $zip_name = sys_get_temp_dir() . DS . 'labels' . time() . uniqid() . '.zip';
            if ($zip->open($zip_name, ZIPARCHIVE::CREATE) !== TRUE) {
            }
            foreach ($arrZPL AS $coll) {
                if (file_exists($coll['name'])) {
                    $zip->addFile($coll['name'], $coll['localname']);
                }
            }
            if ($i > 0 && count($pdf->pages) > 0) {
                $zip->addFromString('pdf_labels_only.pdf', $pdf->render());
            }
            $zip->close();
            if (file_exists($zip_name)) {
                header('Content-type: application/zip');
                header('Content-Disposition: attachment; filename="ups_shipping_labels.zip"');
                readfile($zip_name);
                unlink($zip_name);
            }
        } else if ($i > 0) {
            $pdfData = $pdf->render();
            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf");
            echo $pdfData;
            return true;
        } else {
            return false;
        }
    }

    private function _setLabelToPage($label, $storeId = NULL)
    {
        $image = imagecreatefromstring(file_get_contents($label));

        if (!$image) {
            return false;
        }
        $xSize = imagesx($image);
        $ySize = imagesy($image);
        /*if (Mage::getStoreConfig('upslabel/printing/printer') == "GIF") {*/
        if (Mage::getStoreConfig('upslabel/printing/papersize') != "AC") {
            if (Mage::getStoreConfig('upslabel/printing/papersize') == "A4") {
                if ($xSize > 595) {
                    $ySize = $ySize * (595 / $xSize);
                    $xSize = 595;
                }
                $page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
            } else {
                $page = new Zend_Pdf_Page($xSize, $ySize);
            }
        } else {
            $ySize = Mage::getStoreConfig('upslabel/printing/custom_width') * ($ySize / $xSize);
            $xSize = Mage::getStoreConfig('upslabel/printing/custom_width');
            $page = new Zend_Pdf_Page($xSize, $ySize);
        }

        imageinterlace($image, 0);
        $tmpFileName = sys_get_temp_dir() . DS . 'lbl' . rand(10000, 999999) . '.png';
        imagepng($image, $tmpFileName);
        $image = Zend_Pdf_Image::imageWithPath($tmpFileName);
        $page->drawImage($image, 0, 0, $xSize, $ySize);
        unlink($tmpFileName);
        /*}*/
        return ($page);
    }
}
