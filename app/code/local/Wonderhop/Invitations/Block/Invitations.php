<?php 
 
class Wonderhop_Invitations_Block_Invitations extends Mage_Core_Block_Template {
 
    public function getPersonalLink() {
        $customer = $this->_getSession()->getCustomer();
        return Mage::getUrl() . '?r=' . $customer->getReferralCode();
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
        $invitation_send_date = array();
        
        #search all the invitations sent and add to the result
        foreach($invitations as $invitation) {
            if (isset($result[trim($invitation->getSentTo())])) {
                continue;
            }
            $invitation_send_date[trim($invitation->getSentTo())] = $invitation->getInvitationSendDate();
            #set them as not joined
            $result[trim($invitation->getSentTo())] = 0;
        }
        
        #search all the customers with the referral code
        foreach($customers as $customer) {
            
            #set the customer as joined
            $result[$customer->getEmail()] = 1;
            
            #remove from invitation send date array the joined ones. 
            unset($invitation_send_date[$customer->getEmail()]);
        }
        
        
        #search the other customers which are already registered
        $customers  = Mage::getModel('customer/customer')->getCollection()
                       ->addAttributeToFilter('email', array('in' => array_keys($invitation_send_date)))->load();
                       
        foreach($customers as $customer) {               
            
           if (isset($result[$customer->getEmail()])) {
                if ($invitation_send_date[$customer->getEmail()] > $customer->getCreatedAt()) {
                    $result[$customer->getEmail()] = 2;
                    continue;
                } else {
                    #Joined from someone else's invite
                    $result[$customer->getEmail()] = 3;
                    continue;
               }
           } 
           
        }
        
        return $result;
    }
	 
}
