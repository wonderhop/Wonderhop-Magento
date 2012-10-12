<?php 
 
class Wonderhop_Sales_Block_Registered extends Mage_Core_Block_Template {
    /**
     * Retrieve Customer Session instance
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    
    /*
     * Check if customer is logged in and its the first page after loggin
     */
    
    public function displayBlock() {
        if($this->_getCustomerSession()->isLoggedIn() && Mage::getSingleton('core/session')->getCustomerRegistered()) {
	    Mage::getSingleton('core/session')->unsCustomerRegistered();
            return 1;
        }
    }
    
    public function isLoggedIn() {
        return $this->_getCustomerSession()->isLoggedIn();
    }
    
    public function checkIfCustomerLoggedIn() {
        if($this->_getCustomerSession()->isLoggedIn() && Mage::getSingleton('core/session')->getCustomerLoggedIn()) {
            Mage::getSingleton('core/session')->unsCustomerLoggedIn();
            return 1;
        }
    }
    
    public function isPage($check_page) {
        $page = Mage::app()->getFrontController()->getRequest()->getRouteName();
       
        $identifier = Mage::getSingleton('cms/page')->getIdentifier();
        
        if (!$identifier && $page == $check_page) {
            return 1;
        }
        
        if ($page == 'cms') {
            return $check_page == $identifier; 
        }
    }
    
    public function getVisitorId() {
        return Mage::getSingleton ( 'log/visitor' )->getId();
    }
    
    public function getCustomer() {
        return $this->_getCustomerSession()->getCustomer();
    }

    public function getAd() {
	return $this->getCustomer()->getAd();	
    }  
  
    public function getCustomerInformation($json = True) {
        $customer   = $this->_getCustomerSession()->getCustomer();
        
        $result = array('$email'        => $customer->getEmail(),
                        '$created'      => $customer->getCreatedAt(),
                        '$first_name'   => $customer->getFirstname(),
                        '$id'           => $customer->getId(),
			'$ad'		=> $customer->getAd(),
                        '$utm_source'   => $customer->getUtmSource(),
                        '$utm_campaign' => $customer->getUtmCampaign(),
                        '$utm_medium'   => $customer->getUtmMedium(),
                        '$utm_content'  => $customer->getUtmContent());
                        
        if (str_replace('-', '',$customer->getLastName())) {
            $result['$last_name'] = $customer->getLastname();
        }
        if ($customer->getReferrerId()) {
            $result['$referral'] = $customer->getReferrerId();
        }
        
        
        if (!$json) {
            return $result;
        }
        
        return Mage::helper('core')->jsonEncode($result);
    }
} 
