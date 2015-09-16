<?php

class Pan_JewelryDesigner_ApiController extends Mage_Core_Controller_Front_Action
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

        if ($this->_helper->isEnabled()) {
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
        if (!$this->_helper->isEnabled()) {
            $this->_sendJson(array('success' => false, 'message' => 'Not authorized'), self::UNAUTHORIZED);
            return $this;
        }

        $this->_sendJson($this->_partialsDir, self::SUCCESS);
    }

    /**
     * Check if customer or an admin is logged in and return a boolean value
     *
     * @return boolean
     */
    public function authorizeAction()
    {
        $loggedIn = $this->_helper->isCustomerLoggedIn();
        $this->_sendJson(array('logged_in' => $loggedIn), self::SUCCESS);
    }

    public function productsAction()
    {
        // default JSON string
        $products = '';

        try {
            $params         = $this->getRequest()->getParams();
            $productType    =  str_replace('.json', '', $this->getRequest()->getParam('type', 'bracelets'));
            $limit          = $this->getRequest()->getParam('limit', null);
            $offset         = $this->getRequest()->getParam('offset', null);

            switch ($productType) {
                case 'clips':
                    $catName = 'PANDORA Clips';
                    break;
                case 'spacers':
                    $catName = 'PANDORA Spacers';
                    break;
				case 'bracelets':
                    $catName = 'PANDORA Bracelets';
                    break;
                case 'safetychains':
                case 'safety_chains':
                case 'safety-chains':
                    $catName = 'PANDORA Safety Chains';
                    break;
                case 'charms':
                    $catName = 'PANDORA Charms';
                    break;
                default:
                    $catName = ucwords($productType);
                    break;
            }

            $categoryId = Mage::getSingleton('catalog/category')->loadByAttribute('name', $catName)->getId();
            $apiModel   = Mage::getModel('pan_jewelrydesigner/api_' . $productType);
            $products   = $apiModel->getProducts($categoryId, $limit, $offset);

        } catch (Exception $e) {
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log($e->getMessage());
        }


        $this->_sendJson($products, self::SUCCESS);
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

    public function designsAction()
    {
        $designId           = $this->getRequest()->getParam('id', null);
        $scopeToCustomer    = $this->getRequest()->getParam('scope_to_customer', true);
        $allowAnonymous     = $this->getRequest()->getParam('allow_anonymous', false);

        if ($allowAnonymous) {
            $design = Mage::getModel('pan_jewelrydesigner/design')->load($designId);
            $designData     = $design->getData();
            $data           = (!empty($designData)) ? $designData : null;
        } else {
            $customer           = Mage::helper('pan_jewelrydesigner')->getCustomer();
            $customerId         = (!empty($customer) && $scopeToCustomer) ? $customer->getId() : null;

            $inspirations       = (bool)$this->getRequest()->getParam('inspirations', false);
            $designModel        = Mage::getModel('pan_jewelrydesigner/design');

            if (!empty($designId)) {
                $design         = $designModel->loadDesign($designId, $customerId);
                $designData     = $design->getData();
                $data           = (!empty($designData)) ? $designData : null;
            } else {
                if ($inspirations) {
                    $collection = $designModel->getDesigns(null, true);
                } else {
                    $collection = $designModel->getDesigns($customerId, false);
                }

                // $collection = $designModel->getDesigns($customerId, $inspirations);
                if (!empty($collection)){
                    $designs = $collection->toArray();
                } else {
                    $designs = array();
                }

                $data = (!empty($designs)) ? $designs['items'] : array();
            }
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

    public function cloneDesignAction()
    {
        try {
            $postData           = $this->getRequest()->getPost();
            $designId           = $postData['id'];

            $customer           = Mage::helper('pan_jewelrydesigner')->getCustomer();
            $customerId         = (!empty($customer)) ? $customer->getId() : null;

            if (empty($customerId)) {
                // Response data array
                $data = array(
                    'design_id' => null,
                    'error'     => true,
                    'message'   => 'Please <a href="/customer/account/create">register</a> or <a href="/customer/account/login">login</a> to customize an Inspiration Bracelet.',
                    'status'    => self::SUCCESS
                );
            } else {
                $designModel        = Mage::getModel('pan_jewelrydesigner/design');
                $clonedDesignId     = $designModel->cloneDesign($designId, $customerId);

                // Response data array
                $data = array(
                    'design_id' => $clonedDesignId,
                    'error'     => false,
                    'message'   => 'Successfully copied bracelet!',
                    'status'    => self::SUCCESS
                );
            }
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

    public function addToCartAction()
    {
        try {
            $postData   = $this->getRequest()->getPost();
            $products   = array();

            if (array_key_exists('products', $postData)) {
                $products = Zend_Json::decode($postData['products']);
            }

            $cart = Mage::getSingleton('checkout/cart');
            $cart->init();

            $itemsAddedToCart = 0;
            foreach($products as $sku => $lineItem) {
                /**
                 * only add items that have a 'qty' value greater than 0;
                 *
                 * the PAN.DesignerWorkspace JS class (filename: designer_workspace.js)
                 * will handle the counting of which products and it's instances are
                 * already owned and which are new products that should be purchased
                 */
                if ($lineItem['qty'] > 0) {
                    $_product   = Mage::getModel('catalog/product')->load($lineItem['id']);
                    $params     = array(
                        'qty'       => $lineItem['qty'],
                        'product'   => $lineItem['id']
                    );

                    // configurable product's super attribute options
                    if ($lineItem['type'] === 'bracelet') {
                        $params['super_attribute'] = array();

                        $instance0 = $lineItem['instances'][$lineItem['sku'] . '_0'];
                        if (!empty($instance0) && array_key_exists('super_attributes', $instance0)) {
                            foreach($instance0['super_attributes'] as $attrId => $valueId) {
                                $params['super_attribute'][$attrId] = $valueId;
                            }
                        }
                    }

                    // add the product to the cart
                    $cart->addProduct($_product, $params);
                    // increment our counter
                    $itemsAddedToCart++;
                }
            }

            // update the cart
            $cart->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);

            if ($itemsAddedToCart > 0) {
                $message = 'Successfully added ' . $itemsAddedToCart .  ' products to cart!';
            } else {
                $message = 'No products were added to the cart';
            }

            // Response data array
            $data = array(
                'error'     => false,
                'status'    => self::SUCCESS,
                'message'   => $message
            );

            // refresh the mini-cart on the frontend
            if (!Mage::getSingleton('checkout/session')->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $this->loadLayout();
                    $toplink = $this->getLayout()->getBlock('top.links')->toHtml();
                    $cartTop = $this->getLayout()->getBlock('cart_top')->toHtml();
                    $data['toplink'] = $toplink;
                    $data['cartTop'] = $cartTop;
                }
            }
        } catch (Exception $e) {
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log('[CAUGHT EXCEPTION] : ' . $e->getMessage);

            // Response data array
            $data = array(
                'error'     => true,
                'message'   => $e->getMessage(),
                'status'    => self::INTERNAL_SERVER_ERROR
            );
        }

        $this->_sendJson($data, self::SUCCESS);
    }

    public function addToWishlistAction()
    {
        try {
            $postData   = $this->getRequest()->getPost();
            $products   = array();

            if (array_key_exists('products', $postData)) {
                $products = Zend_Json::decode($postData['products']);
            }

            if (Mage::helper('pan_jewelrydesigner')->isCustomerLoggedIn()) {
                $customer = Mage::helper('pan_jewelrydesigner')->getCustomer();
                $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($customer->getId(), true);

                $itemsAddedToCart = 0;
                foreach($products as $sku => $lineItem) {
                    /**
                     * only add items that have a 'qty' value greater than 0;
                     *
                     * the PAN.DesignerWorkspace JS class (filename: designer_workspace.js)
                     * will handle the counting of which products and it's instances are
                     * already owned and which are new products that should be purchased
                     */
                    if ($lineItem['qty'] > 0) {
                        $_product   = Mage::getModel('catalog/product')->load($lineItem['id']);
                        $params     = array(
                            'qty'       => $lineItem['qty'],
                            'product'   => $lineItem['id']
                        );

                        if ($lineItem['type'] === 'bracelet') {
                            $params['super_attribute'] = array();

                            $instance0 = $lineItem['instances'][$lineItem['sku'] . '_0'];
                            if (!empty($instance0) && array_key_exists('super_attributes', $instance0)) {
                                foreach($instance0['super_attributes'] as $attrId => $valueId) {
                                    $params['super_attribute'][$attrId] = $valueId;
                                }
                            }
                        }

                        // add the item to the wishlist
                        $result = $wishlist->addNewItem($_product, $params);
                        // increment our counter
                        $itemsAddedToCart++;
                    }
                }

                $wishlist->save();

                if ($itemsAddedToCart > 0) {
                    $message = 'Successfully added ' . $itemsAddedToCart .  ' products to wish list!';
                } else {
                    $message = 'No products were added to the wish list';
                }

                // Response data array
                $data = array(
                    'error'             => false,
                    'status'            => self::SUCCESS,
                    'message'           => $message,
                );
            } else {
                // Response data array
                $data = array(
                    'error'             => true,
                    'status'            => self::UNAUTHORIZED,
                    'message'           => 'Please <a href="/customer/account/login">login</a> to save your items to a wish list.',
                );
            }
        } catch (Exception $e) {
            Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
            Mage::log('[CAUGHT EXCEPTION] : ' . $e->getMessage);

            // Response data array
            $data = array(
                'error'             => true,
                'message'           => $e->getMessage(),
                'status'            => self::INTERNAL_SERVER_ERROR
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