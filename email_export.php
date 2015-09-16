<?php

require_once 'app/Mage.php';
umask(0);

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::getSingleton('core/session', array('name' => 'adminhtml'));

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);


// Configuration
$emailFrom = "service@pandoramoa.com";
$emailTo = "service@pandoramoa.com"; // Multiple email addresses can be separated using a comma
$emailSubject = "Magento Export - Shipped Orders";
$emailText = "See attached.";
$folderToExport = "var/oexport"; // Relative to the Magento root directory. ATTENTION: Setting the wrong path will result in all contents of the folder being deleted.
// End Configuration

if (empty($folderToExport)) exit;

$fileArray = array();
// Load files
$filesInExportFolder = glob(Mage::getBaseDir() . DS . $folderToExport . DS . '*');
foreach ($filesInExportFolder as $file) {
    if (is_file($file))
        $fileArray[basename($file)] = file_get_contents($file);
}

if (empty($fileArray)) {
    // Nothing to send
    exit;
}

// Send email
@ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
@ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

$mail = new Zend_Mail('utf-8');

$setReturnPath = Mage::getStoreConfig('system/smtp/set_return_path');
switch ($setReturnPath) {
    case 1:
        $returnPathEmail = $emailFrom;
        break;
    case 2:
        $returnPathEmail = Mage::getStoreConfig('system/smtp/return_path_email');
        break;
    default:
        $returnPathEmail = null;
        break;
}

if ($returnPathEmail !== null) {
    $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
    Zend_Mail::setDefaultTransport($mailTransport);
}

$mail->setFrom($emailFrom, $emailFrom);
foreach (explode(",", $emailTo) as $email) {
    $mail->addTo($email, '=?utf-8?B?' . base64_encode($email) . '?=');
}

foreach ($fileArray as $filename => $data) {
    $attachment = $mail->createAttachment($data);
    $attachment->filename = $filename;
}

$mail->setSubject('=?utf-8?B?' . base64_encode($emailSubject) . '?=');
$mail->setBodyText($emailText);

try {
    $mail->send(Mage::helper('xtcore/utils')->getEmailTransport());
} catch (Exception $e) {
    echo sprintf('Error while sending email: %s', $e->getMessage());
    exit;
}

// Delete files in export folder
foreach ($filesInExportFolder as $file) {
    if (is_file($file))
        unlink($file);
}

?>
