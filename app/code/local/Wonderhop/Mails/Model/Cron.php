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
	
	    $this->getLogger()->log("xxxx");    
	    $active =  Mage::getStoreConfig('Wonderhop_Sales/general/sales_newsletter_active',Mage::app()->getStore());
	    
	    if (!$active) {
	        return;
	    }
	    
	    $templateId = Mage::getStoreConfig('Wonderhop_Sales/general/daily_newsletter_template',Mage::app()->getStore()); 
	    
	    $customers = Mage::getModel('customer/customer')->getCollection()->load();
	    #foreach($customers as $customer) {
        #    $customer = Mage::getModel('customer/customer')->load($customer->getId());
        #}
        $customer = Mage::getModel('customer/customer')->load(18);
        $this->sendTransactionaEmail($templateId, $customer);
    } 
    
	public function sendTransactionaEmail($templateId, $customer, $comment = NULL, $custom_vars = NULL) {
        
        if (!$templateId) {
            die("No Template Id");
        }
        
        // Set sender information
        $senderName = Mage::getStoreConfig('trans_email/ident_general/name');
        $senderEmail =  Mage::getStoreConfig('trans_email/ident_general/email');
        $sender = array('name'  => $senderName,
                        'email' => $senderEmail);
     
     
        $recepientEmail = $customer->getEmail();
 
       
        $recepientName = str_replace('-', '', $customer->getFirstname() . " " . $customer->getLastname());
        
        // Get Store ID
        $storeId = Mage::app()->getStore()->getId();
  
        // Set variables that can be used in email template
        $vars = array(
                        'email'         => $recepientEmail, 
                        'customer_name' => $recepientName, 
                      );
        
        if ($custom_vars) {
            $vars = array_merge($vars, $custom_vars);
        }
        
        $translate  = Mage::getSingleton('core/translate');
        
        // Send Transactional Email
        Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
        
        $translate->setTranslateInline(true);
	}
	           
}
