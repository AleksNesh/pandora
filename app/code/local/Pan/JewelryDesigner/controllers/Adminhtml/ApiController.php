<?php

class Pan_JewelryDesigner_Adminhtml_ApiController extends Mage_Adminhtml_Controller_Action
{
    /**
     * HTTP Response Codes
     */
    // Success
    const SUCCESS               = 200;
    const CREATED               = 201;
    const ACCEPTED              = 202;
    const NO_CONTENT            = 204;
    // redirection
    const MOVED_PERMANENTLY     = 301;
    const REDIRECT_FOUND        = 302;
    const NOT_MODIFIED          = 304;
    // client error
    const BAD_REQUEST           = 400;
    const UNAUTHORIZED          = 401;
    const FORBIDDEN             = 403;
    const NOT_FOUND             = 404;
    const METHOD_NOT_ALLOWED    = 405;
    const REQUEST_TIMEOUT       = 408;
    // server error
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED       = 501;
    const BAD_GATEWAY           = 502;
    const SERVICE_UNAVAILABLE   = 503;
    const GATEWAY_TIMEOUT       = 504;
    const LOOP_DETECTED         = 508;

    /**
     * @var Pan_JewelryDesigner_Helper_Data
     */
    protected $_helper;

    /**
     * Relative URL path to the Pan_JewelryDesigner/app/partials directory
     * @var string
     */
    protected $_partialsDir;

    /**
     * Class constructor
     *
     * @return Pan_JewelryDesigner_ApiController
     */
    public function _construct()
    {
        $this->_helper = Mage::helper('pan_jewelrydesigner');

        if ($this->_helper->isEnabledInAdmin()) {
            $this->_partialsDir = $this->_helper->getAngularAppPartialsDir();
        }
    }

    /**
     * Send relative path (from Magento base url) to our app/partials directory
     *
     * @return string
     */
    public function partialsurlAction()
    {
        if (!$this->_helper->isEnabledInAdmin()) {
            $this->_sendJson(array('success' => false, 'message' => 'Not authorized'), self::UNAUTHORIZED);
            return $this;
        }

        $this->_sendJson($this->_partialsDir, self::SUCCESS);
    }

    public function getTemplateAction()
    {
        $template = array_keys($this->getRequest()->getParams());

        if(!empty($template)) {
            $template = $template[0];
        }

        $baseDir        = Mage::getBaseDir();
        $importReadyDir = $baseDir.'/app/code/local/Pan/JewelryDesigner/app/partials/';
        $fileContent    = file_get_contents($importReadyDir.'/'.$template.'.html');

        // template was not found
        if(!$fileContent) {
            $fileContent =  '<h1>Template [' . $template . '] does not exist.</h1>';
        }

        echo $fileContent;
    }

    /**
     * Check if customer or an admin is logged in and return a boolean value
     *
     * @return boolean
     */
    public function authorizeAction()
    {
        $inAdminArea = Mage::helper('pan_jewelrydesigner')->inAdminArea();

        if($inAdminArea) {
            $adminUser      = Mage::getSingleton('admin/session')->getUser();
            $adminUserId    = ($adminUser) ? $adminUser->getData('user_id') : null;

            $loggedIn       = (!empty($adminUserId)) ? true : false;
        } else {
            $loggedIn = false;
        }

        $this->_sendJson(array('logged_in' => $loggedIn), self::SUCCESS);
    }

    public function designsAction()
    {
        $designId       = $this->getRequest()->getParam('id', null);
        $inspirations   = (bool)$this->getRequest()->getParam('inspirations', false);
        $customerId     = $this->getRequest()->getParam('customer_id', null);

        $designModel    = Mage::getModel('pan_jewelrydesigner/design');
        if (!empty($designId)) {
            $design         = $designModel->loadDesign($designId);
            $designData     = $design->getData();
            $data           = (!empty($designData)) ? $designData : null;
        } else {
            // $customer   = Mage::helper('pan_jewelrydesigner')->getCustomer();
            // $customerId = (!empty($customer)) ? $customer->getId() : null;

            $collection = $designModel->getDesigns(null, $inspirations);
            if (!empty($collection)){
                $designs = $collection->toArray();
            } else {
                $designs = array();
            }

            $data = (!empty($designs)) ? $designs['items'] : array();
        }

        $this->_sendJson($data, self::SUCCESS);
    }

    public function saveDesignAction()
    {
        try {
            $postData       = $this->getRequest()->getPost();
            $designModel    = Mage::getModel('pan_jewelrydesigner/design');
            $designId       = $designModel->saveDesign($postData);

            // Response data array
            $data = array(
                'design_id' => $designId,
                'error'     => false,
                'message'   => 'Successfully saved bracelet!',
                'status'    => self::SUCCESS
            );

        } catch (Exception $e) {
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log('[CAUGHT EXCEPTION] : ' . $e->getMessage);

            // Response data array
            $data = array(
                'design_id' => $designId,
                'error'     => true,
                'message'   => $e->getMessage(),
                'status'    => self::INTERNAL_SERVER_ERROR
            );
        }

        $this->_sendJson($data, self::SUCCESS);
    }

    public function deleteDesignAction()
    {
        try {
            $postData   = $this->getRequest()->getPost();
            $designId   = $postData['id'];
            $model      = Mage::getModel('pan_jewelrydesigner/design');
            $design     = $model->load($designId);


            $customer           = Mage::helper('pan_jewelrydesigner')->getCustomer();
            $adminUserId        = $design->getData('admin_user_id');
            $createdByAnAdmin   = (!empty($adminUserId)) ? true : false;
            $inAdminArea        = Mage::helper('pan_jewelrydesigner')->inAdminArea();

            switch (true) {
                // Allow customer to delete their own designs
                case (!empty($customer) && $customer->getId() === $design->getData('customer_id')):
                    $design->delete();
                    $error      = false;
                    $message    = 'Successfully deleted the design.';
                    break;

                // Allow admin users to delete designs created by other admins
                // if they are in the admin area
                case($inAdminArea && $createdByAnAdmin):
                    $design->delete();
                    $error      = false;
                    $message    = 'ADMIN: Successfully deleted the design.';
                    break;

                // Don't allow admins to destroy designs created by customers
                case($inAdminArea && !$createdByAnAdmin):
                    // $design->delete();
                    $error      = true;
                    $message    = "ADMIN: WARNING! You are trying to delete a customer's design! You are currently not allowed to do this, so the design will remain as is and not be destroyed.";
                    break;

                // Don't allow anything to happen to the design.
                default:
                    $error      = true;
                    $message    = 'You do not have permissions to delete that design.';
                    break;
            }


            // Response data array
            $data = array(
                'design_id' => $designId,
                'error'     => $error,
                'message'   => $message,
                'status'    => self::SUCCESS
            );
        } catch (Exception $e) {
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log('[CAUGHT EXCEPTION] : ' . $e->getMessage);

            // Response data array
            $data = array(
                'design_id' => $designId,
                'error'     => true,
                'message'   => $e->getMessage(),
                'status'    => self::INTERNAL_SERVER_ERROR
            );
        }

        $this->_sendJson($data, self::SUCCESS);
    }

    public function cloneDesignAction()
    {
        try {
            $postData           = $this->getRequest()->getPost();
            $designId           = $postData['id'];

            $designModel        = Mage::getModel('pan_jewelrydesigner/design');
            $clonedDesignId     = $designModel->cloneDesign($designId);

            // Response data array
            $data = array(
                'design_id' => $clonedDesignId,
                'error'     => false,
                'message'   => 'Successfully copied bracelet!',
                'status'    => self::SUCCESS
            );
        } catch (Exception $e) {
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log('[CAUGHT EXCEPTION] : ' . $e->getMessage);

            // Response data array
            $data = array(
                'design_id' => $designId,
                'error'     => true,
                'message'   => $e->getMessage(),
                'status'    => self::INTERNAL_SERVER_ERROR
            );
        }

        $this->_sendJson($data, self::SUCCESS);
    }

    /**
     * Send correct headers and formated data for JSON
     *
     * @param  mixed $data
     * @return void
     */
    protected function _sendJson($data, $responseCode = self::SUCCESS)
    {
        $this->getResponse()
            ->setBody(Zend_Json::encode($data))
            ->setHttpResponseCode($responseCode)
            ->setHeader('Content-type', 'application/json', true);
        return $this;

    }
}
