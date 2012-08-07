<?php class Wonderhop_Mails_Helper_Data extends Mage_Core_Helper_Abstract {
	 
	 public static function sendTransactionalEmail($templateId, $recepientEmail, $customer = NULL, $comment = NULL, $custom_vars = NULL) {
        
        if (!$templateId) {
            die("No Template Id");
        }
        
        // Set sender information
        $senderName = Mage::getStoreConfig('trans_email/ident_general/name');
        $senderEmail =  Mage::getStoreConfig('trans_email/ident_general/email');
        $sender = array('name'  => $senderName,
                        'email' => $senderEmail);
        
        $recepientName = '';
        
        if ($customer) {
            $recepientEmail = $customer->getEmail();
            $recepientName  = str_replace('-', '', $customer->getFirstname() . " " . $customer->getLastname());
        }
        
        if (Mage::getStoreConfig('Wonderhop_Sales/general/test_active',Mage::app()->getStore())) {
            $allowed_emails = Mage::getStoreConfig('Wonderhop_Sales/general/test_emails',Mage::app()->getStore());
            $allowed_emails = explode(',', $allowed_emails);
            if (!in_array($recepientEmail, $allowed_emails)) {
                return;
            }
        }
        
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
