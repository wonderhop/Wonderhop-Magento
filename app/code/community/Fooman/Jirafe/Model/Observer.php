<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Model_Observer
{

    /**
     * initialise tracker information
     * reads visitorId from cookie information for particular site
     * 
     * @param type $storeId
     * @return Fooman_Jirafe_Model_JirafeTracker 
     */
    protected function _initPiwikTracker ($storeId)
    {
        $appToken = Mage::helper('foomanjirafe')->getStoreConfig('app_token');
        $siteId = Mage::helper('foomanjirafe')->getStoreConfig('site_id', $storeId);

        $jirafePiwikUrl = 'http://' . Mage::getModel('foomanjirafe/jirafe')->getPiwikBaseUrl();
        $piwikTracker = new Fooman_Jirafe_Model_JirafeTracker($siteId, $jirafePiwikUrl);
        $piwikTracker->setTokenAuth($appToken);
        
        if(Mage::helper('foomanjirafe')->isDebug() && !$piwikTracker->getVisitorId()){
            if (version_compare(Mage::getVersion(), '1.4.0.0', '>=')) {
                Mage::helper('foomanjirafe')->debug('No Visitor Id for User Agent: '.Mage::helper('core/http')->getHttpUserAgent());
            } else {
                Mage::helper('foomanjirafe')->debug('No Visitor Id for User Agent.');
            }
            Mage::helper('foomanjirafe')->debug($_COOKIE); 
        }
        
        if($piwikTracker->getVisitorId()) {
            //set forced VisitorId to be the ID read from the Cookie
            $piwikTracker->setVisitorId($piwikTracker->getVisitorId());
        }
                
        $piwikTracker->disableCookieSupport();
        $piwikTracker->setAsyncFlag(true);

        return $piwikTracker;
    }

    /**
     * save Piwik visitorId and attributionInfo to order db table
     * for later use
     *
     * @param $observer
     */
    public function salesConvertQuoteToOrder ($observer)
    {
        Mage::helper('foomanjirafe')->debug('salesConvertQuoteToOrder');
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        /* @var $order Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();

        $piwikTracker = $this->_initPiwikTracker($order->getStoreId());
        if (Mage::getDesign()->getArea() == 'frontend') {
            Mage::helper('foomanjirafe')->debug('salesConvertQuoteToOrder Frontend');
            $order->setJirafePlacedFromFrontend(true);
        }
        $order->setJirafeVisitorId($piwikTracker->getVisitorId());
        $order->setJirafeOrigVisitorId($quote->getJirafeOrigVisitorId());
        $order->setJirafeAttributionData($piwikTracker->getAttributionInfo());
        $order->setJirafeIsNew(1);
    }

    /**
     * Check fields in the user object to see if we should run sync
     * use POST data to identify update to existing users
     * only call sync if relevant data has changed
     *
     * @param $observer
     */
    public function adminUserSaveBefore($observer)
    {
        Mage::helper('foomanjirafe')->debug('adminUserSaveBefore');
        $user = $observer->getEvent()->getObject();
        if (Mage::registry('foomanjirafe_sync') || Mage::registry('foomanjirafe_upgrade')) {
            //to prevent a password change unset it here for pre 1.4.0.0
            if (version_compare(Mage::getVersion(), '1.4.0.0') < 0) {
                $user->unsPassword();
            }
            return;
        }

        $jirafeUserId = $user->getJirafeUserId();
        $jirafeToken = $user->getJirafeUserToken();

        $jirafeEnabled = Mage::app()->getRequest()->getPost('jirafe_enabled');
        $jirafeSendEmail = Mage::app()->getRequest()->getPost('jirafe_send_email');
        $jirafeDashboardActive = Mage::app()->getRequest()->getPost('jirafe_dashboard_active');        
        $jirafeEmailReportType = Mage::app()->getRequest()->getPost('jirafe_email_report_type');
        $jirafeEmailSuppress = Mage::app()->getRequest()->getPost('jirafe_email_suppress');
     
        // Check to see if some user fields have changed
        if (!$user->getId() ||
            $user->dataHasChangedFor('firstname') ||
            $user->dataHasChangedFor('username') ||
            $user->dataHasChangedFor('email') ||
            empty($jirafeUserId) ||
            empty($jirafeToken)) {
            if (!Mage::registry('foomanjirafe_sync')) {
                Mage::register('foomanjirafe_sync', true);
            }
        }

        if ($jirafeEnabled != $user->getJirafeEnabled() && $jirafeEnabled != null) {
            $user->setJirafeEnabled($jirafeEnabled);
            //remove the token when a user disables Jirafe
            if(!$jirafeEnabled) {
                $user->setJirafeUserToken(null);
            }
            $user->setDataChanges(true);
            if (!Mage::registry('foomanjirafe_sync')) {
                Mage::register('foomanjirafe_sync', true);
            }
        }

        if ($jirafeSendEmail != $user->getJirafeSendEmail()&& $jirafeSendEmail != null) {
            $user->setJirafeSendEmail($jirafeSendEmail);
            $user->setDataChanges(true);
        }
        if ($jirafeDashboardActive != $user->getJirafeDashboardActive() && $jirafeDashboardActive != null) {
            $user->setJirafeDashboardActive($jirafeDashboardActive);
            $user->setDataChanges(true);
        }        
        if ($jirafeEmailReportType != $user->getJirafeEmailReportType()) {
            $user->setJirafeEmailReportType($jirafeEmailReportType);
            $user->setDataChanges(true);
        }
        if ($jirafeEmailSuppress != $user->getJirafeEmailSuppress()) {
            $user->setJirafeEmailSuppress($jirafeEmailSuppress);
            $user->setDataChanges(true);
        }
    }

    
    /**
     * adminUserSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see adminUserSaveCommitAfter
     * @param type $observer 
     */
    public function adminUserSaveAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->adminUserSaveCommitAfter($observer);
        }
    }    
    
    /**
     * Check to see if we need to sync.  If so, do it.
     *
     * @param $observer
     */
    public function adminUserSaveCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('adminUserSaveAfter');
        if (Mage::registry('foomanjirafe_sync')) {
            Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
        }
    }

    /**
     * adminUserSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see adminUserDeleteCommitAfter
     * @param type $observer 
     */
    public function adminUserDeleteAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->adminUserDeleteCommitAfter($observer);
        }
    }   
    
    /**
     * We need to sync every time after we delete a user
     *
     * @param $observer
     */
    public function adminUserDeleteCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('adminUserDeleteAfter');
        Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
    }

    /**
     * Check fields in the store object to see if we should run sync
     * only call sync if relevant data has changed
     *
     * @param $observer
     */
    public function storeSaveBefore($observer)
    {
        Mage::helper('foomanjirafe')->debug('storeSaveBefore');
        $store = $observer->getEvent()->getStore();
        // If the object is new, or has any data changes, sync
        if (!$store->getId() 
            || $store->hasDataChanges()                         //works for Magento 1.4.1+
            || $store->dataHasChangedFor('is_active')
            || $store->dataHasChangedFor('name')
            || $store->dataHasChangedFor('code')
            || $store->dataHasChangedFor('group_id')
            ) {
            if (!Mage::registry('foomanjirafe_sync')) {
                Mage::register('foomanjirafe_sync', true);
            }
        }
    }

    /**
     * storeSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see storeSaveCommitAfter
     * @param type $observer 
     */
    public function storeSaveAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->storeSaveCommitAfter($observer);
        }
    }     
    
    /**
     * Check to see if we need to sync.  If so, do it.
     *
     * @param $observer
     */
    public function storeSaveCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('storeSaveAfter');
        if (Mage::registry('foomanjirafe_sync')) {
            Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
        }
    }

    
    /**
     * storeDeleteCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see storeDeleteCommitAfter
     * @param type $observer 
     */
    public function storeDeleteAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->storeDeleteCommitAfter($observer);
        }
    }    
    
    /**
     * We need to sync every time after we delete a store
     *
     * @param $observer
     */
    public function storeDeleteCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('storeDeleteCommitAfter');
        Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
    }

    /**
     * websiteDeleteCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see websiteDeleteCommitAfter
     * @param type $observer 
     */
    public function websiteDeleteAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->websiteDeleteCommitAfter($observer);
        }
    }      
    
    /**
     * We need to sync every time after we delete a website
     *
     * @param $observer
     */
    public function websiteDeleteCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('websiteDeleteCommitAfter');
        Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
    }

    /**
     * Check fields in the website object to see if we should run sync
     * only call sync if relevant data has changed
     *
     * @param $observer
     */
    public function websiteSaveBefore($observer)
    {
        Mage::helper('foomanjirafe')->debug('websiteSaveBefore');
        $website = $observer->getEvent()->getWebsite();
        // If the object is new, or has any data changes, sync
        if (!$website->getId() 
            || $website->hasDataChanges()                         //works for Magento 1.4.1+
            || $website->dataHasChangedFor('name')
            || $website->dataHasChangedFor('code')
            || $website->dataHasChangedFor('default_group_id')        
        ){
            if (!Mage::registry('foomanjirafe_sync')) {
                Mage::register('foomanjirafe_sync', true);
            }
        }
    }

    /**
     * websiteSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see websiteSaveCommitAfter
     * @param type $observer 
     */
    public function websiteSaveAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->websiteSaveCommitAfter($observer);
        }
    }      
    
    /**
     * Check to see if we need to sync.  If so, do it.
     *
     * @param $observer
     */
    public function websiteSaveCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('websiteSaveCommitAfter');
        if (Mage::registry('foomanjirafe_sync')) {
            Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
        }
    }

    /**
     * Check fields in the store group object to see if we should run sync
     * only call sync if relevant data has changed
     *
     * @param $observer
     */
    public function storeGroupSaveBefore($observer)
    {
        Mage::helper('foomanjirafe')->debug('storeGroupSaveBefore');
        $storeGroup = $observer->getEvent()->getStoreGroup();
        // If the object is new, or has any data changes, sync
        if (!$storeGroup->getId() 
            || $storeGroup->hasDataChanges()                         //works for Magento 1.4.1+
            || $storeGroup->dataHasChangedFor('name')
            || $storeGroup->dataHasChangedFor('code')
            || $storeGroup->dataHasChangedFor('default_group_id')        
        ){
            if (!Mage::registry('foomanjirafe_sync')) {
                Mage::register('foomanjirafe_sync', true);
            }
        }
    }

    /**
     * storeGroupSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see storeGroupSaveCommitAfter
     * @param type $observer 
     */
    public function storeGroupSaveAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->storeGroupSaveCommitAfter($observer);
        }
    }      
    
    /**
     * Check to see if we need to sync.  If so, do it.
     *
     * @param $observer
     */
    public function storeGroupSaveCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('storeGroupSaveAfter');
        if (Mage::registry('foomanjirafe_sync')) {
            Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
        }
    }

    /**
     * storeGroupDeleteCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     * 
     * @see storeGroupDeleteCommitAfter
     * @param type $observer 
     */
    public function storeGroupDeleteAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->storeGroupDeleteCommitAfter($observer);
        }
    }    
    
    /**
     * We need to sync every time after we delete a store group
     *
     * @param $observer
     */
    public function storeGroupDeleteCommitAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('storeGroupDeleteAfter');
        Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
    }

    /**
     * sync a jirafe store after settings have been saved
     * checks local settings hash for settings before sync
     *
     * @param $observer
     */
    public function configSaveAfter($observer)
    {
        Mage::helper('foomanjirafe')->debug('syncAfterSettingsSave');
        $configData = $observer->getEvent()->getObject();
        if($configData instanceof Mage_Core_Model_Config_Data) {
            $path = $configData->getPath();
            $keys = array('web/unsecure/base_url', 'general/locale/timezone', 'currency/options/base');
            if (in_array($path, $keys)) {
                Mage::getModel('foomanjirafe/jirafe')->syncUsersStores();
            }
        }
    }

    /**
     * we can't add external javascript via normal Magento means
     * adding child elements to the head block or dashboard are also not automatically rendered
     * add foomanjirafe_dashboard_head via this observer
     * add foomanjirafe_dashboard_toggle via this observer
     * add foomanjirafe_adminhtml_permissions_user_edit_tab_jirafe via this observer
     *
     * @param $observer
     */
    public function coreBlockAbstractToHtmlBefore($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $params = array('_relative'=>true);
        if ($area = $block->getArea()) {
            $params['_area'] = $area;
        }
        if ($block instanceof Mage_Adminhtml_Block_Permissions_User_Edit_Tabs) {
            $block->addTab('jirafe_section', array(
                'label'     => Mage::helper('foomanjirafe')->__('Jirafe Analytics'),
                'title'     => Mage::helper('foomanjirafe')->__('Jirafe Analytics'),
                'content'   => $block->getLayout()->createBlock('foomanjirafe/adminhtml_permissions_user_edit_tab_jirafe')->toHtml(),
                'after'     => 'roles_section'
            ));
        }
        if (($block instanceof Mage_Adminhtml_Block_Page_Head || $block instanceof Fooman_Speedster_Block_Adminhtml_Page_Head)
            && strpos($block->getRequest()->getControllerName(), 'dashboard')!==false
        ) {
            $block->setOrigTemplate(Mage::getBaseDir('design').DS.Mage::getDesign()->getTemplateFilename($block->getTemplate(), $params));
            $block->setTemplate('fooman/jirafe/dashboard-head.phtml');
            $block->setFoomanBlock($block->getLayout()->createBlock('foomanjirafe/adminhtml_dashboard_js'));
        }
        if ($block instanceof Mage_Adminhtml_Block_Dashboard) {
            $block->setOrigTemplate(Mage::getBaseDir('design').DS.Mage::getDesign()->getTemplateFilename($block->getTemplate(), $params));
            $block->setTemplate('fooman/jirafe/dashboard-toggle.phtml');
            $block->setFoomanBlock($block->getLayout()->createBlock('foomanjirafe/adminhtml_dashboard_toggle'));
        }
    }

    
    /**
     * add all visible items from a quote as tracked ecommerce items
     * 
     * @param Fooman_Jirafe_Model_JirafeTracker $piwikTracker
     * @param Mage_Sales_Model_Quote $quote 
     */
    protected function _addEcommerceItems($piwikTracker, $quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if($item->getName()){
                //we only want to track the main configurable item
                //but not the subitem
                if($item->getParentItem()) {
                    if ($item->getParentItem()->getProductType() == 'configurable') {
                        continue;
                    }
                }

                $itemPrice = $item->getBasePrice();
                // This is inconsistent behaviour from Magento
                // base_price should be item price in base currency
                // TODO: add test so we don't get caught out when this is fixed in a future release
                if(!$itemPrice || $itemPrice < 0.00001) {
                    $itemPrice = $item->getPrice();
                }
                $piwikTracker->addEcommerceItem(
                    $item->getProduct()->getData('sku'),
                    $item->getName(),
                    Mage::helper('foomanjirafe')->getCategory($item->getProduct()),
                    $itemPrice,
                    $item->getQty()
                );
            }
        }
    }

    /**
     * send tracking information for a shopping basket
     * 
     * @param Mage_Sales_Model_Quote $quote 
     */
    protected function ecommerceCartUpdate($quote)
    {
        $piwikTracker = $this->_initPiwikTracker($quote->getStoreId());
        if($piwikTracker->getVisitorId()) {
            $piwikTracker->setIp($quote->getRemoteIp());
            $piwikTracker->setCustomVariable(1, 'U', Fooman_Jirafe_Block_Js::VISITOR_READY2BUY);

            $this->_addEcommerceItems($piwikTracker, $quote);
            $customerSession = Mage::getSingleton('customer/session');
            if ($customerSession->isLoggedIn()) {
                $billingAddress = $quote->getBillingAddress();
                $shippingAddress = $quote->getShippingAddress();
                if ($billingAddress && $billingAddress->getEmail() && $billingAddress->getFirstname()) {
                    $piwikTracker->setCustomVariable(3, 'email', $billingAddress->getEmail());
                    $piwikTracker->setCustomVariable(4, 'firstName', $billingAddress->getFirstname());
                } elseif ($shippingAddress && $shippingAddress->getEmail() && $shippingAddress->getFirstname()) {
                    $piwikTracker->setCustomVariable(3, 'email', $shippingAddress->getEmail());
                    $piwikTracker->setCustomVariable(4, 'firstName', $shippingAddress->getFirstname());
                } elseif ($quote->getCustomerEmail() && $quote->getCustomerFirstname()) {
                    $piwikTracker->setCustomVariable(3, 'email', $quote->getCustomerEmail());
                    $piwikTracker->setCustomVariable(4, 'firstName', $quote->getCustomerFirstname());
                }
            }
            $piwikTracker->doTrackEcommerceCartUpdate($quote->getBaseGrandTotal());
            $quote->setJirafeVisitorId($piwikTracker->getVisitorId());
        }
    }

    /**
     * event observer when a product has been added to the cart
     * triggers ecommerceCartUpdate via salesQuoteCollectTotalsAfter
     * 
     * @param $observer 
     */
    public function checkoutCartProductAddAfter($observer)
    {
        Mage::getSingleton('customer/session')->setJirafePageLevel(Fooman_Jirafe_Block_Js::VISITOR_READY2BUY);
        if(!Mage::registry('foomanjirafe_update_ecommerce')) {
            Mage::register('foomanjirafe_update_ecommerce', true);
        }
    }

    /**
     * event observer when the cart has been updated
     * triggers ecommerceCartUpdate via salesQuoteCollectTotalsAfter
     * 
     * @param $observer 
     */    
    public function checkoutCartUpdateItemsAfter($observer)
    {
        if(!Mage::registry('foomanjirafe_update_ecommerce')) {
            Mage::register('foomanjirafe_update_ecommerce', true);
        }        
    }

    /**
     * event observer when a product has been updated
     * triggers ecommerceCartUpdate via salesQuoteCollectTotalsAfter
     * 
     * @param $observer 
     */     
    public function checkoutCartProductUpdateAfter($observer)
    {
        if(!Mage::registry('foomanjirafe_update_ecommerce')) {
            Mage::register('foomanjirafe_update_ecommerce', true);
        }        
    }

    /**
     * event observer when a product has been removed
     * triggers ecommerceCartUpdate via salesQuoteCollectTotalsAfter
     * 
     * @param $observer 
     */    
    public function salesQuoteRemoveItem($observer)
    {
        if(!Mage::registry('foomanjirafe_update_ecommerce')) {
            Mage::register('foomanjirafe_update_ecommerce', true);
        }        
    }
    /**
     * trigger ecommerceCartUpdate when any changes to the shopping basket
     * need to be send
     *
     * @param $observer
     */
    public function salesQuoteCollectTotalsBefore ($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if(!Mage::registry('foomanjirafe_update_ecommerce') && $this->abandonedCartInfoNowAvailable($quote)) {
            Mage::register('foomanjirafe_update_ecommerce', true);
        }
    }

    /**
     * trigger ecommerceCartUpdate when any changes to the shopping basket
     * need to be send
     *
     * @param $observer
     */
    public function salesQuoteCollectTotalsAfter ($observer)
    {
        if(Mage::registry('foomanjirafe_update_ecommerce')) {
            $this->ecommerceCartUpdate($observer->getEvent()->getQuote());
            Mage::unregister('foomanjirafe_update_ecommerce');
        }
    }

    protected function abandonedCartInfoNowAvailable($quote)
    {
        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        if($quote->dataHasChangedFor('customer_email') || $quote->dataHasChangedFor('customer_firstname')) {
            return true;
        }
        if ($billingAddress) {
            if( $billingAddress->dataHasChangedFor('email') ||  $billingAddress->dataHasChangedFor('firstname')) {
                return true;
            }
        }
        if ($shippingAddress) {
            if( $shippingAddress->dataHasChangedFor('email') ||  $shippingAddress->dataHasChangedFor('firstname')) {
                return true;
            }
        }
        return false;
    }
}
