<?php 
 chdir("/var/www/Wonderhop-Magento/");
require 'app/Mage.php';
if (!Mage::isInstalled()) {
    // We cannot run the Magento instance if it isn't installed.
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}
// Here you should put unique identifier of store view / store group / website,
// if you leave this field empty, application will use the default value
$initializationCode = 'admin';
// Means that Magento will be initialized on a specified store view
// Also you can use such types like "website" or "group"
// for initialization on specific website or store group
$initializationType = 'store';
// Specifies the scope of you application.
// Magento has three scopes that used in core: "adminhtml", "fontend" and "crontab",
// but you can create your own
$scope = 'frontend';
// Initialize Mage_Core_Model_App object
Mage::app($initializationCode, $initializationType);
// Load configuration
Mage::getConfig()->init();
// Load event observers for specified scope
Mage::getConfig()->loadEventObservers($scope);
// Add event area for event dispatching
Mage::app()->addEventArea($scope);
 
 $x = new Wonderhop_Mails_Model_Cron();
 $x->executeCron();
class Wonderhop_Mails_Model_Cron {
    
    public $logger; 
    public $debug;
    
    public function __construct() {
  
        $this->logger = Mage::getModel('core/log_adapter', 'sales.log');
 
   
    } 
 
    public function getLogger() {
  
        return $this->logger;
    }
    
  	function executeCron () {
	
	   
	    $date = new DateTime(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
	    $active =  Mage::getStoreConfig('Wonderhop_Sales/general/sales_newsletter_active',Mage::app()->getStore());
	    $test   =  Mage::getStoreConfig('Wonderhop_Sales/general/test_active',Mage::app()->getStore());
	    if (!$active) {
	        return;
	    }
	    list ($run_hour, $run_min, $x)  = explode(',', Mage::getStoreConfig('Wonderhop_Sales/general/daily_newsletter_run_time', Mage::app()->getStore()));
	    
        if (!$test && ($date->format('H') != $run_hour || $date->format('i') != $run_min))  {
	        return;
	    }
	     
	     return;
	    $templateId = Mage::getStoreConfig('Wonderhop_Sales/general/daily_newsletter_template',Mage::app()->getStore()); 
	    //Ebizmarts_MageMonkey_Model_Api
	    $template = Mage::getModel('core/email_template');
	    $template->load($templateId);
        $templateProcessed = $template->getProcessedTemplate(array(), true);
        
        $type = 'regular';
        $opts['list_id'] = '236ee2fdd6';
        $opts['subject'] = 'Test Newsletter Subject';
        $opts['from_email'] = 'andrei@sinapticode.ro'; 
        $opts['from_name']  = 'ACME, Inc.';
        $opts['tracking']   = array('opens' => true, 'html_clicks' => true, 'text_clicks' => false);
        $opts['title'] = 'Test Newsletter Title';
        $content = array('html'=> $templateProcessed, 
		  'text' => 'text text text *|UNSUB|*'
		);
        
        Mage::getSingleton('monkey/api', array('store' => 1))
									->campaignCreate($type, $opts, $content);
		return;							
	    $customers = Mage::getModel('customer/customer')->getCollection()->load();
	    foreach($customers as $customer) {
            $customer = Mage::getModel('customer/customer')->load($customer->getId());
            Mage::helper('mails')->sendTransactionalEmail($templateId, null, $customer);
        }
     
    } 
  
	           
}
