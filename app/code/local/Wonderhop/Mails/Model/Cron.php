<?php 
 
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
	    
	    if (!$active) {
	        return;
	    }
	    list ($run_hour, $run_min, $x)  = explode(',', Mage::getStoreConfig('Wonderhop_Sales/general/daily_newsletter_run_time', Mage::app()->getStore()));
	    
        if (($date->format('H') != $run_hour || $date->format('i') != $run_min))  {
	        return;
	    }
	    
	    $templateId = Mage::getStoreConfig('Wonderhop_Sales/general/daily_newsletter_template',Mage::app()->getStore()); 
	    
	    $customers = Mage::getModel('customer/customer')->getCollection()->load();
	    foreach($customers as $customer) {
            $customer = Mage::getModel('customer/customer')->load($customer->getId());
            Mage::helper('mails')->sendTransactionalEmail($templateId, null, $customer);
        }
     
    } 
  
	           
}
