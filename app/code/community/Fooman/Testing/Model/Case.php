<?php

class Fooman_Testing_Model_Case extends EcomDev_PHPUnit_Test_Case
{
    public function mockAdminQuoteSession()
    {
        $adminQuoteSessionMock = $this->getModelMock(
            'foomantesting/session_quote',
            array(
                'init'
            )
        );

        $adminQuoteSessionMock->expects($this->any())
            ->method('init')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'foomantesting/session_quote', $adminQuoteSessionMock);
    }

    /**
     * Creates admin user session stub for testing adminhtml controllers
     *
     * @param array $aclResources list of allowed ACL resources for user,
     *                            if null then it is super admin
     * @param int $userId fake id of the admin user, you can use different one if it is required for your tests
     * @return EcomDev_PHPUnit_Test_Case_Controller
     */
    protected function mockAdminUserSession(array $aclResources = null, $userId = 1)
    {
        $adminSessionMock = $this->getModelMock(
            'admin/session',
            array(
                'init',
                'getUser',
                'isLoggedIn',
                'isAllowed')
        );

        $adminUserMock = $this->getModelMock(
            'admin/user',
            array('login', 'getId', 'save', 'authenticate', 'getRole')
        );

        $adminRoleMock = $this->getModelMock('admin/roles', array('getGwsIsAll'));

        $adminRoleMock->expects($this->any())
            ->method('getGwsIsAll')
            ->will($this->returnValue(true));

        $adminUserMock->expects($this->any())
            ->method('getRole')
            ->will($this->returnValue($adminRoleMock));

        $adminUserMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($userId));

        $adminSessionMock->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($adminUserMock));

        $adminSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->will($this->returnValue(true));

        // Simple isAllowed implementation
        $adminSessionMock->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnCallback(
                function($resource) use ($aclResources) {
                    if ($aclResources === null) {
                        return true;
                    }
                    if (strpos($resource, 'admin/') === 0) {
                        $resource = substr($resource, strlen('admin/'));
                    }
                    return in_array($resource, $aclResources);
                }));


        $this->replaceByMock('model', 'admin/session', $adminSessionMock);

        /*$this->getRequest()->setParam(
            Mage_Adminhtml_Model_Url::SECRET_KEY_PARAM_NAME,
            Mage::getSingleton('adminhtml/url')->getSecretKey()
        );*/

        return $adminSessionMock;
    }

    /**
     * Creates information in the session,
     * that customer is logged in
     *
     * @param string $customerEmail
     * @param string $customerPassword
     * @return Mage_Customer_Model_Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockCustomerSession($customerId, $storeId = null)
    {
        // Create customer session mock, for making our session singleton isolated
        $customerSessionMock = $this->getModelMock('customer/session', array('renewSession','init'));
        $this->replaceByMock('singleton', 'customer/session', $customerSessionMock);

        if ($storeId === null) {
            $storeId = $this->app()->getAnyStoreView()->getCode();
        }

        $this->setCurrentStore($storeId);
        $customerSessionMock->loginById($customerId);

        return $customerSessionMock;
    }

    public function mockCoreSession()
    {
        $coreSessionMock = $this->getModelMock(
            'core/session',
            array(
                'init'
            )
        );

        $coreSessionMock->expects($this->any())
            ->method('init')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'core/session', $coreSessionMock);
    }

    protected function _testConfigAndDb($path, $value)
    {
        $configCollection = Mage::getModel('core/config_data')->getCollection()
            ->AddFieldToFilter('scope', Mage_Core_Model_Store::DEFAULT_CODE)
            ->AddFieldToFilter('scope_id', Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->AddFieldToFilter('path', $path)
            ->load();
        $this->assertGreaterThanOrEqual(1, count($configCollection), $path.' not found in DB');
        $dbValue = $configCollection->getFirstItem()->getValue();
        $this->assertEquals($dbValue, $value, $path.' stored in DB does not match ');
        $configValue = Mage::getStoreConfig($path);
        $this->assertEquals($configValue, $value, $path.' stored in loaded config does not match');

    }
}