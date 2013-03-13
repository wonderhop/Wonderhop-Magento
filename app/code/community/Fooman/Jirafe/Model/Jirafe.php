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

class Fooman_Jirafe_Model_Jirafe
{

    const STATUS_ORDER_NOT_EXPORTED = 0;
    const STATUS_ORDER_EXPORTED = 1;
    const STATUS_ORDER_FAILED = 2;

    const EVENTS_BATCH_SIZE  = 5;
    const EVENTS_IMPORT_SIZE = 10;

    private $_jirafeApi = false;

    // PRODUCTION environment
    const JIRAFE_API_SERVER = 'https://api.jirafe.com';
    const JIRAFE_API_BASE = '';
    const JIRAFE_PIWIK_BASE_URL = 'data.jirafe.com';
    const JIRAFE_JS_BASE_URL = 'c.jirafe.com';

    // DEV environment
//    const JIRAFE_API_SERVER = 'http://api.jirafe.local';
//    const JIRAFE_API_BASE = 'app_dev.php';
//    const JIRAFE_PIWIK_BASE_URL = 'piwik.local';
//    const JIRAFE_JS_BASE_URL = 'c.jirafe.com';

    // TEST environment
//    const JIRAFE_API_SERVER = 'https://test-api.jirafe.com';
//    const JIRAFE_API_BASE = '';
//    const JIRAFE_PIWIK_BASE_URL = 'test-data.jirafe.com';
//    const JIRAFE_JS_BASE_URL = 'c.jirafe.com';

    const JIRAFE_API_VERSION = 'v1';
    const JIRAFE_DOC_URL = 'http://jirafe.com/doc';

    function __construct ()
    {
        try {
            // register autoloader
            Jirafe_Autoloader::register();
            $this->_jirafeApi = new Jirafe_Client(
                                    Mage::helper('foomanjirafe')->getStoreConfig('app_token'), 
                                    new Fooman_Jirafe_Model_HttpConnection_Zend(self::JIRAFE_API_SERVER . '/v1')
                                );
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('foomanjirafe')->setStoreConfig('last_status_message', $e->getMessage());
            Mage::helper('foomanjirafe')->setStoreConfig('last_status',
                    Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_ERROR);
        }
    }

    //TODO: move url generation into php-client

    /**
     * Returns the URL of the API
     *
     * @param string $entryPoint An optional entry point
     *
     * @return string
     */
    public function getApiUrl ($entryPoint = null)
    {
        // Server
        $url = rtrim(self::JIRAFE_API_SERVER, '/');

        // Base
        if ((boolean) self::JIRAFE_API_BASE) {
            $url.= '/' . ltrim(self::JIRAFE_API_BASE, '/');
        }

        // Version
        if ((boolean) self::JIRAFE_API_VERSION) {
            $url.= '/' . ltrim(self::JIRAFE_API_VERSION, '/');
        }

        // Entry Point
        if (null !== $entryPoint) {
            $url.= '/' . ltrim($entryPoint, '/');
        }

        return $url;
    }

    /**
     * Returns the URL of the asset corresponding to the specified filename
     *
     * @param string $filename The filename of the asset
     *
     * @return string
     */
    public function getAssetUrl ($filename)
    {
        return rtrim(self::JIRAFE_API_SERVER, '/') . '/' . ltrim($filename, '/');
    }

    /**
     * Returns the URL of the piwik installation
     *
     * @return string
     */
    public function getPiwikBaseUrl ()
    {
        return rtrim(self::JIRAFE_PIWIK_BASE_URL, '/');
    }

    /**
     * Returns the URL to send call me back notice to
     *
     * @return string
     */
    public function getCMBUrl ()
    {
        return 'https://'.$this->getPiwikBaseUrl().'/cmb';
    }

    /**
     * Returns the URL to send call me back notice to
     *
     * @return string
     */
    public function getEventsUrl ()
    {
        return 'https://'.$this->getPiwikBaseUrl().'/events';
    }

    public function sendCMB($siteId)
    {
        $parts = parse_url($this->getCMBUrl());
        $parts['query'] = 'siteId='.urlencode($siteId);

        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80,
            $errno, $errstr, 30);

        if (!$fp) {
            return false;
        } else {
            $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
            $out.= "Host: " . $parts['host'] . "\r\n";
            $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out.= "Content-Length: " . strlen($parts['query']) . "\r\n";
            $out.= "Connection: Close\r\n\r\n";
            if (isset($parts['query'])) {
                $out.= $parts['query'];
            }

            fwrite($fp, $out);
            fclose($fp);
            return true;
        }
    }

    public function checkEventsToken($token, $hash)
    {
        if ($hash == sha1($token . Mage::helper('foomanjirafe')->getStoreConfig('app_token'))) {
            return true;
        }
        Mage::helper('foomanjirafe')->debug('Could not verify event token and hash ' . $hash . ' vs ' . sha1($token . Mage::helper('foomanjirafe')->getStoreConfig('app_token')));
        return false;
    }

    /**
     * Returns the URL of the Jirafe JS wrapper
     *
     * @return string
     */
    public function getJsBaseUrl ()
    {
        return rtrim(self::JIRAFE_JS_BASE_URL, '/');
    }

    /**
     * construct documenation url for given version, platform and type (user or troubleshooting)
     *
     * @param string $platform
     * @param string $type
     * @param string $version
     * @return string
     */
    public function getDocUrl ($platform, $type='user', $version=null)
    {
        if ($version) {
            return rtrim(self::JIRAFE_DOC_URL, '/') . "/{$platform}/{$version}/" . ltrim($type,
                    '/');
        } else {
            return rtrim(self::JIRAFE_DOC_URL, '/') . "/{$platform}/" . ltrim($type,
                    '/');
        }
    }

    public function getJirafeApi()
    {
        if(!$this->_jirafeApi) {
            throw new Exception('Jirafe API not initialised.');
        }
        return $this->_jirafeApi;
    }

    /**
     * check if Magento instance has a jirafe application id, create one if none exists
     * update jirafe server if any parameters have changed
     *
     * @return string $appId
     */
    public function checkAppId ()
    {
        $defaultStoreId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        //check if we already have a jirafe application id for this Magento instance
        $appId = Mage::helper('foomanjirafe')->getStoreConfig('app_id');
        $currentHash = $this->_createAppSettingsHash($defaultStoreId);
        $changeHash = false;

        if ($appId) {
            //check if settings have changed
            if ($currentHash != Mage::helper('foomanjirafe')->getStoreConfig('app_settings_hash')) {
                try {
                    $baseUrl = Mage::helper('foomanjirafe')->getUnifiedStoreBaseUrl(Mage::getStoreConfig('web/unsecure/base_url', $defaultStoreId));
                    $return = $this->getJirafeApi()->applications($appId)->update(array('url' => $baseUrl));
                    $changeHash = true;
                } catch (Exception $e) {
                    Mage::logException($e);
                    Mage::helper('foomanjirafe')->setStoreConfig('last_status_message', $e->getMessage());
                    Mage::helper('foomanjirafe')->setStoreConfig('last_status', Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_ERROR);
                    return false;
                }
            }
        } else {
            //retrieve new application id from jirafe server
            try {
                $baseUrl = Mage::helper('foomanjirafe')->getUnifiedStoreBaseUrl(Mage::helper('foomanjirafe')->getStoreConfigDirect('web/unsecure/base_url', $defaultStoreId,false));
                $platformData = Mage::helper('foomanjirafe')->getPlatformData();
                $return = $this->getJirafeApi()->applications()->create(
                    Mage::helper('foomanjirafe')->getStoreDescription(Mage::app()->getStore($defaultStoreId)),
                    $baseUrl,
                    $platformData['platform_type'],
                    $platformData['platform_version'],
                    $platformData['plugin_version']
                );
                if(empty($return['app_id']) || empty($return['token'])) {
                    throw new Exception ('Jirafe did not return a valid application Id or token.');
                }
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('foomanjirafe')->setStoreConfig('last_status_message', $e->getMessage());
                Mage::helper('foomanjirafe')->setStoreConfig('last_status', Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_ERROR);
                return false;
            }
            Mage::helper('foomanjirafe')->setStoreConfig('app_id', $return['app_id']);
            Mage::helper('foomanjirafe')->setStoreConfig('app_token', $return['token']);
            $this->getJirafeApi()->setToken($return['token']);
            $appId = $return['app_id'];
            $changeHash = true;
            Mage::helper('foomanjirafe')->setStoreConfig('last_status_message', Mage::helper('foomanjirafe')->__('Application successfully set up'));
            Mage::helper('foomanjirafe')->setStoreConfig('last_status', Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_APP_TOKEN_RECEIVED);
        }

        //save updated hash
        if ($changeHash) {
            Mage::helper('foomanjirafe')->setStoreConfig('app_settings_hash', $currentHash);
        }
        return $appId;
    }

    /**
     * create a md5 hash of the the default store (admin) settings we store server side so we know when we need to update
     *
     * @param int $storeId
     * @return string
     */
    protected function _createAppSettingsHash ($storeId)
    {
        $baseUrl = Mage::helper('foomanjirafe')->getUnifiedStoreBaseUrl(Mage::getStoreConfig('web/unsecure/base_url', $storeId));
        return md5($baseUrl . Mage::app()->getStore($storeId)->getName());
    }


    /**
     * create a md5 hash of the the store's url settings we store server side so we know when we need to update
     *
     * @param int $storeId
     * @return string
     */
    protected function _createStoreSettingsHash ($storeId)
    {
        $baseUrl = Mage::helper('foomanjirafe')->getUnifiedStoreBaseUrl(Mage::getStoreConfig('web/secure/base_url', $storeId));
        return md5($baseUrl);
    }

    public function getAdminUsers()
    {
        $adminUserArray = array();
        $adminUsers = Mage::getSingleton('admin/user')->getCollection();
        foreach ($adminUsers as $adminUser) {
            //For us to add a User to Jirafe the following needs to have happened
            //1. the admin user is currently active in Jirafe
            //2. they have an email address
            //3. Jirafe is enabled for the user
            //4. the user has opted into receiving Advanced Analytics by Jirafe
            if ($adminUser->getIsActive() &&  $adminUser->getEmail() && $adminUser->getJirafeEnabled() && $adminUser->getJirafeOptinAnswered()) {
                $tmpUser = array();
                if( $adminUser->getJirafeUserToken()) {
                    $tmpUser['token'] = $adminUser->getJirafeUserToken();
                }
                $tmpUser['username'] = Mage::helper('foomanjirafe')->createJirafeUserId($adminUser);
                $tmpUser['email'] = Mage::helper('foomanjirafe')->createJirafeUserEmail($adminUser);
                $tmpUser['first_name'] = $adminUser->getFirstname();
                $tmpUser['last_name'] = $adminUser->getLastname();
                //$tmpUser['mobile_phone'] = $adminUser->getMobilePhone();
                $adminUserArray[] = $tmpUser;
            }
        }

        return $adminUserArray;
    }

    public function getStores()
    {
        $storeArray = array();
        $stores = Mage::helper('foomanjirafe')->getStores();
        foreach ($stores as $storeId => $store) {
            $websiteId = $store->getWebsiteId();
            $tmpStore = array();
            $tmpStore['site_id'] = Mage::helper('foomanjirafe')->getStoreConfigDirect('site_id', $storeId);
            $tmpStore['external_id'] = $storeId;
            $tmpStore['description'] = Mage::helper('foomanjirafe')->getStoreDescription($store);
            //newly created stores don't fall back on global config values
            $tmpStore['url'] = Mage::helper('foomanjirafe')->getUnifiedStoreBaseUrl(Mage::helper('foomanjirafe')->getStoreConfigDirect('web/unsecure/base_url', $storeId, false, $websiteId));
            $tmpStore['timezone'] = Mage::helper('foomanjirafe')->getStoreConfigDirect('general/locale/timezone', $storeId, false, $websiteId);
            $tmpStore['currency'] = Mage::helper('foomanjirafe')->getStoreConfigDirect('currency/options/base', $storeId, false, $websiteId);
            $storeArray[] = $tmpStore;
        }

        return $storeArray;
    }

    /**
     * Save user info that has come back from the Jirafe sync process.  Only save information that changed, so that we do not
     * kick off another sync process.
     */
    public function saveUserInfo($jirafeUsers)
    {
        if(!empty($jirafeUsers)) {
            foreach ($jirafeUsers as $jirafeUser) {
                $email = Mage::helper('foomanjirafe')->getUserEmail($jirafeUser['email']);
                $adminUser = Mage::getModel('admin/user')->load($email,'email');
                if ($adminUser->getId()) {
                    $changed = false;
                    if ($jirafeUser['email'] != $adminUser->getJirafeUserID()) {
                        $adminUser->setJirafeUserId($jirafeUser['email']);
                        $changed = true;
                    }
                    if ($jirafeUser['token'] != $adminUser->getJirafeUserToken()) {
                        $adminUser->setJirafeUserToken($jirafeUser['token']);
                        $changed = true;
                    }
                    if ($changed) {
                        //to prevent a password change unset it here for pre 1.4.0.0
                        if (version_compare(Mage::getVersion(), '1.4.0.0') < 0) {
                            $adminUser->unsPassword();
                        }
                        $adminUser->save();
                    }
                }
            }
        }
    }
    /**
     * Save store info that has come back from the Jirafe sync process.  Only save information that changed, so that we do not
     * kick off another sync process.
     * Trigger update of getevents url
     */
    public function saveStoreInfo($jirafeSites, $appId)
    {
        if(!empty($jirafeSites)) {
            foreach ($jirafeSites as $jirafeSite) {
                $store = Mage::app()->getStore($jirafeSite['external_id'])->load($jirafeSite['external_id']);
                // Site ID
                $siteId = Mage::helper('foomanjirafe')->getStoreConfig('site_id', $store->getId());
                if ($siteId != $jirafeSite['site_id']) {
                    Mage::helper('foomanjirafe')->setStoreConfig('site_id', $jirafeSite['site_id'], $store->getId());
                }
                
                // Send store API URL
                $storeJirafeApiUrl = trim((string)$store->getUrl('foomanjirafe/events', array('_secure'=>true, '_nosid'=>true)));
                if (strpos($storeJirafeApiUrl, 'http') !== 0){
                    //Magento can in some versions return an empty base url during the installation routine - /foomanjirafe/events
                    $storeJirafeApiUrl = Mage::helper('foomanjirafe')->getStoreConfigDirect('web/secure/base_url', $store->getId(),false).$storeJirafeApiUrl;
                }
                if(substr_count($storeJirafeApiUrl, '://') != 1){
                    //in some cases we produce a doubled up base url, try this method instead - https://example.com/https://example.com/foomanjirafe/events
                    $storeJirafeApiUrl = rtrim(Mage::helper('foomanjirafe')->getStoreConfigDirect('web/secure/base_url', $store->getId(),false), '/').'/index.php/foomanjirafe/events';
                }

                // Send store Cart URL
                $storeCartUrl = trim((string)$store->getUrl('foomanjirafe/cart', array('_secure'=>false, '_nosid'=>true)));
                if (strpos($storeCartUrl, 'http') !== 0){
                    //Magento can in some versions return an empty base url during the installation routine
                    $storeCartUrl = Mage::helper('foomanjirafe')->getStoreConfigDirect('web/unsecure/base_url', $store->getId(),false).$storeCartUrl;
                }
                if(substr_count($storeJirafeApiUrl, '://') != 1){
                    //in some cases we produce a doubled up base url, http://example.com/http://example.com/checkout/cart
                    //try this method instead
                    $storeCartUrl = rtrim(Mage::helper('foomanjirafe')->getStoreConfigDirect('web/unsecure/base_url', $store->getId(),false), '/').'/index.php/checkout/cart';
                }
                Mage::helper('foomanjirafe')->debug('Store API URL ' . $storeJirafeApiUrl);
                Mage::helper('foomanjirafe')->debug('Store Cart URL ' . $storeCartUrl);
                $this->getJirafeApi()->applications($appId)->sites()->get($jirafeSite['site_id'])->update(array('store_api_url'=>$storeJirafeApiUrl, 'store_cart_url'=>$storeCartUrl));

                // Call CMB for the store
                $this->sendCMB($siteId);
            }
        }
    }

    public function syncUsersStores()
    {
        if (!Mage::registry('foomanjirafe_sync_run')) {
            Mage::register('foomanjirafe_sync_run', true);

            $appId = Mage::helper('foomanjirafe')->getStoreConfig('app_id');
            if (empty($appId)) {
                $appId = $this->checkAppId();
            }
            if(!$appId) {
                return false;
            }
            $adminToken = Mage::helper('foomanjirafe')->getStoreConfig('app_token');

            $this->getJirafeApi()->setToken($adminToken);
            $userArray = $this->getAdminUsers();
            $storeArray = $this->getStores();

            try {
                $this->getJirafeApi()->getConnection()->setConfig(array('timeout'=>120));
                $return = $this->getJirafeApi()->applications($appId)->resources()->sync(
                    $storeArray,
                    $userArray,
                    Mage::helper('foomanjirafe')->getPlatformData() + array('opt_in' => true)
                );
                $this->saveUserInfo($return['users']);
                $this->saveStoreInfo($return['sites'], $appId);
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('foomanjirafe')->setStoreConfig('last_status_message', $e->getMessage());
                Mage::helper('foomanjirafe')->setStoreConfig('last_status', Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_ERROR);
                return false;
            }
            Mage::helper('foomanjirafe')->setStoreConfig('last_status_message', Mage::helper('foomanjirafe')->__('Jirafe sync completed successfully'));
            Mage::helper('foomanjirafe')->setStoreConfig('last_status', Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_SYNC_COMPLETED);

            return true;
        }
    }

    public function sendLogUpdate ($data)
    {
        return true;
        //return $this->getJirafeApi()->getLog()->sendLog(Mage::helper('foomanjirafe')->getStoreConfig('app_token'), $data);
    }

    /**
     * only sync once at the end of the installation or upgrade routine
     * @param string $upgradeVersion
     */
    public function initialSync ($upgradeVersion = '0.1.0')
    {
        if ($upgradeVersion == (string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version) {
            // Once complete, reinit config files
            // reloading the config on earlier Magento versions causes an infinite loop
            if (version_compare(Mage::getVersion(), '1.3.4.0') > 0) {
                Mage::app()->getConfig()->reinit();
            }
            //Make sure the default (admin) store is loaded
            $defaultStoreId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
            Mage::app()->getStore($defaultStoreId)->load($defaultStoreId);
            try {
                $this->syncUsersStores();
                // Run cron for the first time since the upgrade, so that users can see any changes right away.
                if(!Mage::helper('foomanjirafe')->getStoreConfig('installed')) {
                    // Notify user
                    $this->_notifyAdminUser(Mage::helper('foomanjirafe')->isOk(), (string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version);
                    Mage::helper('foomanjirafe')->setStoreConfig('installed', true);
                }
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('foomanjirafe')->setStoreConfig('last_status_message', $e->getMessage());
                Mage::helper('foomanjirafe')->setStoreConfig('last_status',
                        Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_ERROR);

                return false;
            }
        }
    }

    private function _notifyAdminUser ($success, $version)
    {
        if ($success) {
            Mage::getModel('adminnotification/inbox')
                    ->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE)
                    ->setTitle('Jirafe plugin for Magento installed successfully.')
                    ->setDateAdded(gmdate('Y-m-d H:i:s'))
                    ->setUrl($this->getDocUrl('magento','user',$version))
                    ->setDescription('We have just installed Jirafe. Please see the user guide for details.')
                    ->save();
        } else {
            Mage::getModel('adminnotification/inbox')
                    ->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE)
                    ->setTitle('Jirafe plugin for Magento installed - needs configuration')
                    ->setDateAdded(gmdate('Y-m-d H:i:s'))
                    ->setUrl($this->getDocUrl('magento','troubleshooting',$version))
                    ->setDescription('We have just installed Jirafe and but were unable to set it up automatically. Please see the troubleshooting guide.')
                    ->save();
        }
    }

    public function postEvents($token, $siteId, $version)
    {
        $this->createHistoricalEvents($siteId);

        $jirafeEvents = Mage::getModel('foomanjirafe/event')->getCollection()
            ->addFieldToFilter('site_id', $siteId)
            ->addFieldToFilter('version', array('gteq' => $version))
            ->setPageSize(self::EVENTS_BATCH_SIZE)
            ->setCurPage(1)
            ->setOrder('version', 'ASC');

        $events = array();
        foreach ($jirafeEvents as $jirafeEvent) {
            while ($version < $jirafeEvent->getVersion()) {
                Mage::helper('foomanjirafe')->debug('Filling gaps in version numbers! Missing: ' . $version);
                $events[] = array(
                    'v' => $version,
                    'a' => Fooman_Jirafe_Model_Event::JIRAFE_ACTION_NOOP
                );
                $version++;
            }
            $events[] = array(
                'v' => (int)$jirafeEvent->getVersion(),
                'a' => $jirafeEvent->getAction(),
                'd' => json_decode($jirafeEvent->getEventData())
            );
            $version = $jirafeEvent->getVersion()+1;
        }

        $client = new Zend_Http_Client($this->getEventsUrl());
        $client->setParameterPost('token', $token);
        $client->setParameterPost('siteId', $siteId);
        $client->setParameterPost('events', json_encode($events));
        $client->setParameterPost('timestamp', Mage::getSingleton('core/date')->gmtTimestamp());
        Mage::helper('foomanjirafe')->debugEvent(json_encode($events));
        try {
            $response = $client->request('POST');
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        if ($response->isError()) {
            throw new Exception(sprintf("Remote replied with %d %s", $response->getStatus(), $response->getMessage()));
        }

        return true;
    }

    public function createHistoricalEvents($siteId)
    {
        try {
            $storeId = Mage::helper('foomanjirafe')->getStoreIdFromJirafeSiteId($siteId);
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('foomanjirafe')->debug($e->getMessage());
            return;
        }

        $types = array(
            'sales/order'            => 'orderImportCreate',
            'sales/order_creditmemo' => 'refundImportCreate',
        );

        foreach($types as $type => $method) {
            $data = Mage::getModel($type)
                ->getCollection()
                ->setOrder('created_at', 'DESC')
                ->setPageSize(self::EVENTS_IMPORT_SIZE)
                ->setCurPage(1)
                ->addAttributeToFilter('store_id', $storeId)
                ->addAttributeToFilter(
                    'jirafe_export_status', array('or' => array(
                        0 => array('eq' => 0),
                        1 => array('is' => new Zend_Db_Expr('null')))
                    ),
                    'left'
                );

            if (count($data)) {
                Mage::getModel('foomanjirafe/event')->$method($siteId, $data);
            }
        }
    }

    public function getOrderSyncStatus()
    {
        $appId = Mage::helper('foomanjirafe')->getStoreConfig('app_id');
        if ($appId) {
            $stores = Mage::helper('foomanjirafe')->getStores();
            $sitesSyncStatus = array();
            foreach ($stores as $storeId => $store) {
                $siteId = Mage::helper('foomanjirafe')->getStoreConfig('site_id', $storeId);
                if ($siteId) {
                    try {
                        $sitesSyncStatus[$siteId] = $this->getJirafeApi()->applications($appId)->sites($siteId)->orders()->status()->fetch();
                    } catch (Exception $e) {
                        $sitesSyncStatus[$siteId] = array('ok' => 0, 'errors' => $e->getMessage(), 'version' => 'N/A');
                    }
                }
            }
            return $sitesSyncStatus;
        }

    }
}
