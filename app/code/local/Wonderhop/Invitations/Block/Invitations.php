<?php 
 
class Wonderhop_Invitations_Block_Invitations extends Mage_Core_Block_Template {
 
    public function getPersonalLink() {
        $customer = $this->_getSession()->getCustomer();
        return Mage::getUrl('?r=' . $customer->getReferralCode());
    }
    
    private function _getSession() {
        return  Mage::getSingleton('customer/session');
    }
    
    public function getAllInviters() {
        $customer = $this->_getSession()->getCustomer();
        
        $invitations = Mage::getModel('invitations/invitations')->getCollection()
                       ->addFieldToFilter('customer_fk', $customer->getId());
        
        #registered customers having this friend code
        $customers   = Mage::getModel('customer/customer')->getCollection()
                       ->addAttributeToFilter('referrer_id', $customer->getReferralCode());
        
        $result = array();
        foreach($invitations as $invitation) {
            if (isset($result[trim($invitation->getSentTo())])) {
                continue;
            }
            $result[trim($invitation->getSentTo())] = 0;
        }
        foreach($customers as $customer) {
         
            $result[$customer->getEmail()] = 1;
        }
        
        return $result;
    }
	 
}
