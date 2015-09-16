<?php

/**
 * Simple module for custom override of giftcard imports.
 *
 * @category    Pan
 * @package     Pan_Giftcards
 * @copyright   Copyright (c) 2014 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


// This is needed since Varien used a layout that is not easily auto-loadable
include_once("Webtex/Giftcards/controllers/Adminhtml/CardsloadController.php");


class Pan_Giftcards_Adminhtml_CardsloadController extends Webtex_Giftcards_Adminhtml_CardsloadController
{
    /**
     * define constants to use in place of 'magic' numbers
     * during the import process
     *
     * Example of a csv file for import
     *
     * ==================================================
     * || Card Code || Amount || Balance ||  Reference  ||
     * ==================================================
     * ||  NC250116 || 75.00  ||   60.00 || (optional)  ||
     * ==================================================
     * ||  NC250141 || 90.00  ||   50.00 ||             ||
     * ==================================================
     * ||  NC250325 || 88.00  ||   35.00 ||   CG344773  ||
     * ==================================================
     */

    const GIFT_CARD_CODE_COLUMN      = 0;
    const GIFT_CARD_AMOUNT_COLUMN    = 1;
    const GIFT_CARD_BALANCE_COLUMN   = 2;
    const GIFT_CARD_REFERENCE_COLUMN = 3;

    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards');
        $this->_addBreadcrumb($this->__('Import Gift Cards'), $this->__('Import Gift Cards'));
        $this->_addContent($this->getLayout()->createBlock('pan_giftcards/adminhtml_cardsload'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        $request = $this->getRequest();

        $params = $request->getParams();

        $path       = '';

        $delimiter  = $request->getParam('delimiter', false);
        $enclosure  = $request->getParam('enclosure', false);

        try
        {
            if(@empty($_FILES['file']['name']))
            {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giftcards')->__('An error occurred while importing Gift Cards.'));
                $this->getResponse()->setRedirect($this->getUrl('giftcards/adminhtml_giftcards/index'));
                return;
            }

            $file = $_FILES['file']['name'];

            if(empty($file))
            {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giftcards')->__('An error occurred while importing Gift Cards.'));
                $this->getResponse()->setRedirect($this->getUrl('giftcards/adminhtml_giftcards/index'));
                return;
            }

            $path = Mage::getBaseDir('var').DS.'import'.DS;
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);
            $uploader->save($path, $file);

            $io = new Varien_Io_File();
            $io->open(array('path' => $path));
            $io->streamOpen($path.$file, 'r');
            $io->streamLock(true);

            $map = $io->streamReadCsv($delimiter, $enclosure);

            while($data = $io->streamReadCsv($delimiter, $enclosure))
            {

                $giftCardCode   = trim($data[self::GIFT_CARD_CODE_COLUMN]);
                $amount         = trim($data[self::GIFT_CARD_AMOUNT_COLUMN]);
                $balance        = trim($data[self::GIFT_CARD_BALANCE_COLUMN]);
                $reference      = trim($data[self::GIFT_CARD_REFERENCE_COLUMN]);

                // The data index is the columns from the file being
                // imported eg:
                // first column is gift card code, it will be find at data[self::GIFT_CARD_CODE_COLUMN]
                // and so forth
                if($giftCardCode)
                {

                    // Here we can check if we should override
                    // update or skip gift card import if it
                    // already exist in the database.
                    $existingGiftCard = $this->giftCardExist($giftCardCode, true);

                    if(!$existingGiftCard)
                    {
                        // A gift card with this code was not
                        // found we are good to proceed and
                        // import/update this giftCard
                        $model = Mage::getModel('giftcards/giftcards');
                        $model->setCardReference($reference);
                        $model->setCardBalance($this->checkBalance($amount, $balance));
                        $model->setCardAmount($amount);
                        $model->setCardCode($giftCardCode);
                        $model->setCardStatus(1);
                        $model->save();
                    }
                    else
                    {
                        if($params['import_action'] === 'update')
                        {
                            // Update gift card on duplicate
                            $existingGiftCard->setCardReference($reference);
                            $existingGiftCard->setCardBalance($this->checkBalance($amount, $balance));
                            $existingGiftCard->setCardAmount($amount);
                            $existingGiftCard->setCardCode($giftCardCode);
                            $existingGiftCard->setCardStatus(1);
                            $existingGiftCard->save();
                        }

                        // skip option...
                        continue;
                    }
                }
                else
                {
                    // $giftCardCode not valid...
                    continue;
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('giftcards')->__('Gift Cards where succesfully imported '));
        }

        catch (Mage_Core_Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('giftcards')->__($e->getMessage().' An error occurred while importing Gift Cards.'));
        }


        $this->getResponse()->setRedirect($this->getUrl('giftcards/adminhtml_giftcards/index'));
    }

    /**
     *
     * Search for a giftcard in the database by its code
     *
     * @param  string  $giftCardCode   The giftCard code
     * @param  boolean $returnGiftCard If we should return object (giftCard if found) or boolean
     * @return mixed                   Will retur object (giftCard) if $returnGiftCard is true boolean otherwize
     *
     */
    public function giftCardExist($giftCardCode, $returnGiftCard = false)
    {
        $giftCard = Mage::getModel('giftcards/giftcards')
                        ->getCollection()
                        ->addFieldToFilter('card_code', $giftCardCode)
                        ->getFirstItem();

        if( is_object($giftCard) and $giftCard->getId() !== null)
        {
            return $returnGiftCard ? $giftCard : true;
        }

        return false;
    }

    /**
     * Checks if the balance is empty. If empty it will return
     * the amount.
     * @param  double $amount   double
     * @param  double $balance double
     * @return double          amount for balance field
     */
    public function checkBalance($amount, $balance)
    {
        // check for the balance column
        // if it is empty the balance should
        // be the save as amount.
        $value = (empty($balance) && $balance !== '0' && $balance !== 0) ? $amount : $balance;

        return $value;
    }

    // This is to fix an assumption in Magento that breaks translations
    protected function _getRealModuleName()
    {
        return "Pan_Giftcards";
    }

}

