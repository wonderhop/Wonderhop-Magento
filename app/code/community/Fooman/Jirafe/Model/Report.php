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

class Fooman_Jirafe_Model_Report extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_TEMPLATE = 'foomanjirafe/report_email_template';
    const XML_PATH_EMAIL_IDENTITY = 'foomanjirafe/report_email_identity';

    protected $_helper = '';

    protected function _construct ()
    {
        $this->_init('foomanjirafe/report');
        $this->_helper = Mage::helper('foomanjirafe');
        $this->_jirafe = Mage::getModel('foomanjirafe/jirafe');
    }

    public function cron ()
    {
        $this->_helper->debug('starting jirafe report cron');

        // Get the GMT timestamp for this cron - make sure we only get it once for all stores just in case
        $gmtTs = Mage::getSingleton('core/date')->gmtTimestamp();

        // Set flag to make sure we were successful in reporting and logging all stores
        $success = true;

        //check if we have a current applicationi id for jirafe
        $appId = $this->_jirafe->checkAppId();
        if(!$appId) {
            $this->_helper->debug("no Jirafe application ID present - abort cron.");
            $success = false;
        } else {
            //loop over stores to create reports
            $storeCollection = Mage::getModel('core/store')->getCollection();
            foreach ($storeCollection as $store) {
                // Only continue if the store is active
                if ($store->getIsActive()) {
                    // Get the store ID
                    $storeId = $store->getId();
                    // Only continue if this plugin is active for the store 
                    if ($this->_helper->getStoreConfig('isActive', $storeId)) {
                        // Set the current store
                        Mage::app()->setCurrentStore($store);
                        // Get the timespan (array ('from', 'to')) for this report
                        $timespan = $this->_getReportTimespan($store, $gmtTs);
                        // Only continue if the report does not already exist
                        $exists = Mage::getResourceModel('foomanjirafe/report')->getReport($storeId, $timespan['date']);
                        if (!$exists) {
                            try {
                                // Create new report
                                $data = $this->_compileReport($store, $timespan);
                                // Save report
                                $this->_saveReport($store, $timespan, $data);
                                // Send out emails
                                $data_formatted = $this->_getReportDataFormatted($data);
                                // Are we sending a simple or a detailed report?
                                $detailReport = $this->_helper->getStoreConfig('reportType', $storeId) == 'detail';
                                // Email the report to users
                                $this->_emailReport($store, $timespan, $data + $data_formatted + array('detail_report' => $detailReport));
                                // Send Jirafe heartbeat
                                $this->_sendReport($store, $timespan, $data);
                                //save status message
                                $this->_helper->setStoreConfig('last_status_message',
                                        $this->_helper->__("Successfully sent report for %s for %s", $data['store_name'], $timespan['date'])
                                    );
                            } catch (Exception $e) {
                                Mage::logException($e);
                                $success = false;
                                //save status message
                                $this->_helper->setStoreConfig('last_status_message',
                                        $this->_helper->__("Encountered errors sending report for %s", $this->_helper->getStoreDescription($store))
                                    );
                            }
                        } else {
                            $this->_helper->debug("The report for store ID {$storeId} already exists for {$timespan['date']}.  Discontinuing processing for this report.");
                        }
                    }
                }
            }
        }

        $this->_helper->debug('finished jirafe report cron');
    }

    protected function _compileReport ($store, $timespan)
    {
        $reportData = array();

        // Get the day we are running the report
        $from = $timespan['from'];
        $to = $timespan['to'];
        $reportData['date'] = $timespan['date'];

        // Get store information
        $storeId = $store->getId();
        $reportData['store_id'] = $storeId;
        $reportData['site_id'] = $this->_helper->getStoreConfig('site_id', $storeId);
        $reportData['store_name'] = $this->_helper->getStoreDescription($store);
        $reportData['store_url'] = $store->getConfig('web/unsecure/base_url');

        // Tell debugger we are kicking off the report compilation
        $this->_helper->debug("Compiling report for store [{$storeId}] {$reportData['store_name']} on {$reportData['date']}");

        // Get the currency
        $reportData['currency'] = $store->getConfig('currency/options/base');

        // Get version information
        $reportData['plugin_version'] = (string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version;
        $reportData['platform_version'] = Mage::getVersion();
        $reportData['platform_type'] = 'magento';

        // Get the email addresses where the email will be sent
        $reportData['admin_emails'] = $this->_helper->collectJirafeEmails(true,false, true);

        // Get the URL to the Magento admin console, Jirafe settings
        $reportData['jirafe_settings_url'] = Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit/section/foomanjirafe', array('_nosecret' => true, '_nosid' => true));

        // Get the timezone for this store
        $reportData['timezone'] = $store->getConfig('general/locale/timezone');
		
        // Get the language for this store
        $reportData['language'] = $store->getconfig('general/locale/code');

        // Get customer data
        $reportData['customer_num'] = Mage::getResourceModel('foomanjirafe/report')->getStoreUniqueCustomers($storeId, $from, $to);

        // Get refund data
        $reportData += Mage::getResourceModel('foomanjirafe/report')->getStoreRefunds($storeId, $from, $to);

        // Get revenue data
        $reportData += Mage::getResourceModel('foomanjirafe/report')->getStoreRevenues($storeId, $from, $to);
        $reportData['sales_gross'] = $reportData['sales_grand_total'] - $reportData['sales_discounts'];  // Discounts is a negative number so gross will be >= grand total
        $reportData['sales_net'] = $reportData['sales_subtotal'] - $reportData['refunds_subtotal'];

        // Get abandoned cart data
        $reportData += Mage::getResourceModel('foomanjirafe/report')->getStoreAbandonedCarts($storeId, $from, $to);

        // Get order data
        $reportData += Mage::getResourceModel('foomanjirafe/report')->getStoreOrders($storeId, $from, $to);

        // Get visitor and conversion data
        $reportData['visitor_num'] = Mage::getResourceModel('foomanjirafe/report')->getStoreVisitors($storeId, $from, $to);
        if ($reportData['visitor_num'] > 0) {
            $reportData['visitor_conversion_rate'] = $reportData['customer_num'] / $reportData['visitor_num'];
            $reportData['sales_grand_total_per_visitor'] = $reportData['sales_grand_total'] / $reportData['visitor_num'];
            $reportData['sales_net_per_visitor'] = $reportData['sales_net'] / $reportData['visitor_num'];
        } else {
            $reportData['visitor_conversion_rate'] = 0;
            $reportData['sales_grand_total_per_visitor'] = 0;
            $reportData['sales_net_per_visitor'] = 0;
        }

        // Calculate revenue per customer
        if ($reportData['customer_num'] > 0) {
            $reportData['sales_grand_total_per_customer'] = $reportData['sales_grand_total'] / $reportData['customer_num'];
            $reportData['sales_net_per_customer'] = $reportData['sales_net'] / $reportData['customer_num'];
        } else {
            $reportData['sales_grand_total_per_customer'] = 0;
            $reportData['sales_net_per_customer'] = 0;
        }

        if ($reportData['order_num'] > 0) {
            $reportData['sales_grand_total_per_order'] = $reportData['sales_grand_total'] / $reportData['order_num'];
            $reportData['sales_net_per_order'] = $reportData['sales_net'] / $reportData['order_num'];
        } else {
            $reportData['sales_grand_total_per_order'] = 0;
            $reportData['sales_net_per_order'] = 0;
        }
        return $reportData;
    }

    protected function _saveReport ($store, $timespan, $data)
    {
        //save report for transmission
//		$this->_helper->debug($storeData);
        Mage::getModel('foomanjirafe/report')
                ->setStoreId($store->getId())
                ->setStoreReportDate($timespan['date'])
                ->setGeneratedByJirafeVersion($data['plugin_version'])
                ->setReportData(json_encode($data))
                ->save();
    }

    protected function _emailReport ($store, $timespan, $data)
    {
        // Make sure email is active at a global level
        if (Mage::helper('foomanjirafe')->isEmailActive()) {
            // Get the store ID
            $storeId = $store->getId();
            // Get the template
            $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            // Pass in 'true' if there are no orders, so to suppress emails who do not want to be sent for stores with 0 orders
            $containsOrders = !($data['order_num'] == 0);
            // Get the list of emails to send this report
            $emails = $this->_helper->collectJirafeEmails(false, $containsOrders);
            //add intro to the first email being sent (global)
            $data['first'] = !Mage::helper('foomanjirafe')->getStoreConfig('sent_initial_email');
            // Translate email
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            $emailTemplate = Mage::getModel('core/email_template');
            /* @var $emailTemplate Mage_Core_Model_Email_Template */
            foreach ($emails as $emailAddress => $reportType) {
                $this->_helper->debug("Sending ".$reportType." Report to ".$emailAddress);
                $data['detail_report'] = $reportType;
                $emailTemplate
                        ->setDesignConfig(array('area' => 'backend'))
                        ->sendTransactional(
                                $template,
                                Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId),
                                trim($emailAddress),
                                null,
                                $data,
                                $storeId
                );
            }
            if(!$data['first']) {
                // Set a flag to know that the we have sent out the first email
                $this->_helper->setStoreConfig('sent_initial_email', true);
            }
        }
    }

    protected function _getReportTimespan ($store, $gmtTs, $span='day')
    {
        // Get the current timestamp (local time) for this store
        $ts = Mage::getSingleton('core/date')->timestamp($gmtTs);
        $offset = $ts - $gmtTs;
        $fromUnix = strtotime('yesterday', $ts) - $offset;
        $toUnix = strtotime('+1 day', $fromUnix);
        $from = date('Y-m-d H:i:s', $fromUnix);
        $to = date('Y-m-d H:i:s', $toUnix);
        $date = date('Y-m-d', $fromUnix + $offset);

        return array('from' => $from, 'to' => $to, 'date' => $date);
    }

    protected function _sendReport ($store, $timespan, $data)
    {
        return Mage::getModel('foomanjirafe/jirafe')->sendLogUpdate($data);
    }

    protected function _getReportDataFormatted ($data)
    {
        $fdata = array();

        // Get the currency locale so that we can format currencies correctly
        $currencyLocale = Mage::getModel('directory/currency')->load($data['currency']);

        // Make formatted values for reports
        $zendDate = new Zend_Date(strtotime($data['date']), null, Mage::app()->getLocale()->getLocale());
        $fdata['date_formatted'] = $zendDate->toString(Mage::app()->getLocale()->getDateFormat('medium'));
        $fdata['visitor_conversion_rate_formatted'] = sprintf("%01.2f", $data['visitor_conversion_rate'] * 100);

        $currencyFormatItems = array(
            'abandoned_cart_grand_total',
            'order_max',
            'order_min',
            'refunds_discounts',
            'refunds_grand_total',
            'refunds_shipping',
            'refunds_subtotal',
            'refunds_taxes',
            'sales_discounts',
            'sales_grand_total',
            'sales_gross',
            'sales_net',
            'sales_grand_total_per_customer',
            'sales_grand_total_per_order',
            'sales_grand_total_per_visitor',
            'sales_net_per_customer',
            'sales_net_per_order',
            'sales_net_per_visitor',
            'sales_shipping',
            'sales_subtotal',
            'sales_taxes'
        );

        foreach ($currencyFormatItems as $item) {
            $fdata[$item . '_formatted'] = $currencyLocale->formatTxt($data[$item]);
        }

        return $fdata;
    }

}
