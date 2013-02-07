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

class Fooman_Jirafe_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FOOMANJIRAFE_SETTINGS = 'foomanjirafe/settings/';
    const JIRAFE_STATUS_NOT_INSTALLED = '0';
    const JIRAFE_STATUS_ERROR = '1';
    const JIRAFE_STATUS_APP_TOKEN_RECEIVED = '2';
    const JIRAFE_STATUS_SYNC_COMPLETED = '3';

    /**
     * Return store config value for key
     *
     * @param $key
     * @param $storeId
     * @return mixed
     */
    public function getStoreConfig ($key,
            $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $path = self::XML_PATH_FOOMANJIRAFE_SETTINGS . $key;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Return store config value for key directly from db
     *
     * @param $key
     * @param $storeId
     * @param bool $foomanjirafe
     * @param bool $websiteId
     * @return mixed
     */
    public function getStoreConfigDirect ($key,
            $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID, $foomanjirafe=true, $websiteId=false)
    {
        if($foomanjirafe) {
            $path = self::XML_PATH_FOOMANJIRAFE_SETTINGS . $key;
        } else {
            $path = $key;
        }
        $collection = Mage::getModel('core/config_data')->getCollection()
                        ->addFieldToFilter('path', $path)
                        ->addFieldToFilter('scope_id', $storeId);
        if ($storeId != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
            $collection->addFieldToFilter('scope', Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES);
        }
        if ($collection->load()->getSize() == 1) {
            //value exists /-> update/ should only be one
            foreach ($collection as $existingConfigData) {
                return $existingConfigData->getValue();
            }
        }
        //fall back on website and default for non-jirafe settings
        if(!$foomanjirafe) {
            $collection = Mage::getModel('core/config_data')->getCollection()
                            ->addFieldToFilter('path', $path)
                            ->addFieldToFilter('scope_id',  $websiteId)
                            ->addFieldToFilter('scope', Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES);
            if ($collection->load()->getSize() == 1) {
                //value exists /-> update/ should only be one
                foreach ($collection as $existingConfigData) {
                    return $existingConfigData->getValue();
                }
            }
            $collection = Mage::getModel('core/config_data')->getCollection()
                            ->addFieldToFilter('path', $path)
                            ->addFieldToFilter('scope_id',  Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID);
            if ($collection->load()->getSize() == 1) {
                //value exists /-> update/ should only be one
                foreach ($collection as $existingConfigData) {
                    return $existingConfigData->getValue();
                }
            }
        }
    }

    /**
     * Save store config value for key
     *
     * @param string $key
     * @param string $value
     * @param $storeId
     * @return mixed
     */
    public function setStoreConfig ($key, $value,
            $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $path = self::XML_PATH_FOOMANJIRAFE_SETTINGS . $key;

        //save to db
        try {
            $configModel = Mage::getModel('core/config_data');
            $collection = $configModel->getCollection()
                            ->addFieldToFilter('path', $path)
                            ->addFieldToFilter('scope_id', $storeId);
            if ($storeId != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                $collection->addFieldToFilter('scope', Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES);
            }

            if ($collection->load()->getSize() > 0) {
                //value already exists -> update
                foreach ($collection as $existingConfigData) {
                    $existingConfigData->setValue($value)->save();
                }
            } else {
                //new value
                $configModel
                        ->setPath($path)
                        ->setValue($value);
                if ($storeId != Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID) {
                    $configModel->setScopeId($storeId);
                    $configModel->setScope(Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES);
                }
                $configModel->save();
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        Mage::app()->getConfig()->removeCache();
        //we also set it as a temporary item so we don't need to reload the config
        return Mage::app()->getStore($storeId)->load($storeId)->setConfig($path, $value);
    }

    /**
     * Check if we have an app_id and app_token
     *
     * @param $storeId
     * @return bool
     */
    public function isConfigured ($storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        return ($this->getStoreConfig('app_id', $storeId) && $this->getStoreConfig('app_token', $storeId));
    }

    /**
     * get platform/plugin data that can be used with some Jirafe Client methods
     *
     * @return array
     */
    public function getPlatformData()
    {
        return array(
            'platform_type'    => 'magento',
            'platform_version' => Mage::getVersion(),
            'plugin_version'   => (string)Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version,
        );
    }

    /**
     * get list of active store ids
     * @see getStores
     * @return string
     */
    public function getStoreIds ()
    {
        return $this->getStores(true);
    }

    /**
     * get an array of stores or a csv string of store ids
     *
     * @param bool $idsOnly
     * @return array|string
     */
    public function getStores ($idsOnly = false)
    {
        // Get a list of store IDs to send to the user
        $stores = Mage::getModel('core/store')->getCollection();
        $storearr = array();
        foreach ($stores as $store) {
            // Only continue if the store is active
            if ($store->getIsActive()) {
                if ($idsOnly) {
                    $storearr[] = $store->getId();
                } else {
                    $storearr[$store->getId()] = $store;
                }
            }
        }
        if ($idsOnly) {
            return implode(',', $storearr);
        } else {
            ksort($storearr);
            return $storearr;
        }
    }

    /**
     * get the store id for a given Jirafe site id
     *
     * @param int $siteId
     * @return string
     */
    public function getStoreIdFromJirafeSiteId($siteId)
    {
        foreach ($this->getStores() as $storeId => $store) {
            if ($siteId == Mage::helper('foomanjirafe')->getStoreConfigDirect('site_id', $storeId)) {
                return $storeId;
            }
        }
        throw new Exception ('Site ID '.$siteId.' is not associated with any store.');
    }

    /**
     * return admin email addresses that we are emailing the reports to
     * either return a csv list of just emails or an array
     * with key = email address and value = report type
     *
     * @param bool $asCsv
     * @param bool $containsOrders
     * @param bool $allUsers
     * @return array|string
     */
    public function collectJirafeEmails ($asCsv = true, $containsOrders = true, $allUsers = false)
    {
        $adminUsers = Mage::getSingleton('admin/user')->getCollection();
        $emails = array();
        // loop over all admin users
        foreach ($adminUsers as $adminUser) {
            if ($adminUser->getIsActive() && ($adminUser->getJirafeSendEmail() || $allUsers)) {
                // If user wants to suppress emails with no revenue...
                $suppress = $adminUser->getJirafeEmailSuppress();
                // Only continue if the data contains orders, or if we are not suppressing
                if (($containsOrders || !$suppress) || $allUsers) {
                    if ($asCsv) {
                        $emails[] = $adminUser->getEmail();
                    } else {
                        $emails[$adminUser->getEmail()] = $adminUser->getJirafeEmailReportType();
                    }
                }
            }
        }
        // add users added via global config
        if(!$allUsers){
            foreach (explode(',', Mage::helper('foomanjirafe')->getStoreConfig('also_send_emails_to')) as $jirafeEmail) {
                if (!empty($jirafeEmail)) {
                    $emails[$jirafeEmail] = Mage::helper('foomanjirafe')->getStoreConfig('report_type');
                }
            }
        }
        if ($asCsv) {
            return implode(',', $emails);
        } else {
            return $emails;
        }
    }

    /**
     * send message to jirafe.log
     *
     * @param $mesg
     */
    public function debug ($mesg)
    {
        if ($this->isDebug()) {
            Mage::log($mesg, null, 'jirafe.log');
        }
    }

    /**
     * keep entry in jirafe-event-fail.log with error message, trace and event data
     *
     * @param Exception $exception
     * @param $event
     */
    public function debugFailedEvent(Exception $exception, $event, $withTrace = true)
    {
        Mage::log('--------------------------------------------------------------------------------------', null, 'jirafe-event-fail.log');
        Mage::log($exception->getMessage(), null, 'jirafe-event-fail.log');
        if ($withTrace) {
            Mage::log($exception->getTrace(), null, 'jirafe-event-fail.log');
        }
        Mage::log($event, null, 'jirafe-event-fail.log');
        Mage::log('--------------------------------------------------------------------------------------', null, 'jirafe-event-fail.log');
    }

    /**
     * run events through validator
     *
     * @param JSON string $event
     */
    public function debugEvent($event, $withTrace = true)
    {
        if ($this->isDebug()) {
            try {
                $validator = new Jirafe_Event_Validator();
                $validator->run($event);
            } catch (Exception $e) {
                Mage::helper('foomanjirafe')->debugFailedEvent($e, json_encode($event), $withTrace);
            }
        }
    }

    /**
     * are we currently in debug mode
     * @see Admin Back-end > System > Configuration > Jirafe Analytics
     *
     * @return bool
     */
    public function isDebug ()
    {
        return (bool) $this->getStoreConfig('is_debug');
    }

    /**
     * return a unified store description including the website's name
     * @param $store
     * @return string
     */
    public function getStoreDescription ($store)
    {
        return Mage::getModel('core/store_group')->load($store->getGroupId())->getName() . ' (' . $store->getData('name') . ')';
    }

    /**
     * previously used to create a fake unique email address
     * @param $user
     * @return mixed
     */
    public function createJirafeUserId ($user)
    {
        return trim($user->getEmail());
    }

    /**
     * previously used to create a fake unique email address
     *
     * @param $user
     * @return mixed
     */
    public function createJirafeUserEmail ($user)
    {
        return $this->createJirafeUserId($user);
    }

    /**
     * previously used to determine real address from fake email address
     * @see createJirafeUserId
     * @param $email
     * @return mixed
     */
    public function getUserEmail ($email)
    {
        /*
         * see createJirafeUserId
         */

        return trim($email);
    }

    public function getCustomerHash($email)
    {
        return md5('jirafe*'.strtolower(trim($email)));
    }

    /**
     * unify the baseUrl
     * also deal with deprecated notation of {{base_url}}
     * as well as cases where we don't yet have the value
     *
     * @param $baseUrl
     * @return string
     */
    public function getUnifiedStoreBaseUrl ($baseUrl)
    {
        if ($baseUrl == '{{base_url}}' || empty($baseUrl)) {
            $baseUrl = str_replace('index.php', '', Mage::getBaseUrl());
        }
        return rtrim($baseUrl, '/');
    }

    /**
     * run a list of checks to work out if the Jirafe Dashboard
     * should be shown
     *
     * @return bool
     */
    public function isDashboardActive ()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if($user) {
            // To check if the dashboard is active, you must check:
            // 1. If the plugin is active
            // 2. If the plugin is enabled for this user
            // 3. If the dashboard is active for this user
            // 4. And the user has answered the opt-in question
            // 5. If we have a valid app_id and app_token
            return (
                Mage::helper('foomanjirafe')->getStoreConfig('isActive') &&
                $this->currentUserEnabled() &&
                $user->getJirafeDashboardActive() &&
                $user->getJirafeOptinAnswered() &&
                $this->isConfigured()
            );
        }
        return false;
    }

    /**
     * email is active for a store if both
     * isEmailActive
     * isActive
     * are set to yes for the store
     *
     * @return bool
     */
    public function isEmailActive ()
    {
        return (Mage::helper('foomanjirafe')->getStoreConfig('isEmailActive') && Mage::helper('foomanjirafe')->getStoreConfig('isActive'));
    }

    /**
     * check if we have encountered any errors
     * and that a configuration is present
     *
     * @return bool
     */
    public function isOk ()
    {
        return $this->isConfigured()
                && $this->getStatus() != Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_NOT_INSTALLED
                && $this->getStatus() != Fooman_Jirafe_Helper_Data::JIRAFE_STATUS_ERROR;
    }

    /**
     * has the current User enabled Jirafe?
     *
     * @return bool
     */
    public function currentUserEnabled()
    {
        $adminSession = Mage::getSingleton('admin/session');
        if ($adminSession) {
            $user = $adminSession->getUser();
            if ($user) {
                return $user->getJirafeEnabled();
            }
        }
        return false;
    }

    /**
     * return true if the last sync was unsuccessful
     * @return bool
     */
    public function noSync ()
    {
        return $this->getStatus() != self::JIRAFE_STATUS_SYNC_COMPLETED;
    }

    /**
     * return the last status of interacting with Jirafe's Api
     *
     * @return string
     */
    public function getStatus ()
    {
        return $this->getStoreConfig('last_status');
    }

    /**
     * return concatenated string of category names for a product
     *
     * @param type $product
     * @return string
     */
    public function getCategory($product)
    {
        $id = current($product->getCategoryIds());
        $category = Mage::getModel('catalog/category')->load($id);
        $aCategories = array();
        foreach ($category->getPathIds() as $k => $id) {
            // Skip null and root
            if ($k > 1) {
                $category = Mage::getModel('catalog/category')->load($id);
                $aCategories[] = $this->toUTF8($category->getName());
            }
        }
        return join('/', $aCategories);
    }

    /**
     * returns a valid UTF8 string, either by converting from the store default
     * charset or cleaning up non compliant characters
     *
     * @param string $string
     * @param int $storeId
     * @return string
     */
    public function toUTF8($string, $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        if (strlen($string) > 0) {
            $utf8 = @iconv('UTF-8', 'UTF-8', $string);
            if ($string != $utf8) {
                // Not UTF-8
                $storeCharset = Mage::getStoreConfig("api/config/charset", $storeId);
                if (empty($storeCharset) || preg_match('/^utf-?8$/i', $storeCharset)) {
                    // No charset, or charset wrongly reported as utf-8
                    $storeCharset = 'ISO-8859-1';
                }
                return @iconv($storeCharset, 'UTF-8', $string);
            }
        }
        return $string;
    }

    public function formatAmount($amount)
    {
        return sprintf("%01.4f", $amount);
    }
}
