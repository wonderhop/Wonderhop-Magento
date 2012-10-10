<?php class Wonderhop_Invitations_Helper_Data extends Mage_Core_Helper_Abstract {
     
     
     public function giveInviterEnabled() {
        return Mage::getStoreConfigFlag('Wonderhop_Invitations/general/inviter_incentive_active');
     }
     public function getInviterAmount() {
        return Mage::getStoreConfig('Wonderhop_Invitations/general/inviter_reward');
     }
     
     public function giveInviteeEnabled() {
        return Mage::getStoreConfigFlag('Wonderhop_Invitations/general/invitee_incentive_active');
     }
     
     public function getInviteeAmount() {
        return Mage::getStoreConfig('Wonderhop_Invitations/general/invitee_reward');
     }
    
     public function rewardCustomers($registered_customer_id) {
         
         $registered_customer = Mage::getModel('customer/customer')->load($registered_customer_id);
         #inviter customers having this friend code
         $inviter = Mage::getModel('customer/customer')->getCollection()
                   ->addAttributeToFilter('referral_code', $registered_customer->getReferrerId())->getFirstItem();
         
         if (!$inviter) {
            Mage::throwException(sprintf("Customer with generated code %s doesn't exists invited customer id %d", $customer->getReferrerId(), $customer->getId()));
         }
                      
         if (!$registered_customer->getReferrerId()) {
            return;
         }
         
         if($this->giveInviteeEnabled()) {
            $amount = $this->getInviteeAmount();
            $comment = sprintf("Customer registered from invitation. Receives %s credits", $amount);
            $this->giveCredits($registered_customer_id, $amount, $comment);
         }
          
         if($this->giveInviterEnabled()) {
            $amount = $this->getInviterAmount();
            
            #get number of already registered customers with this referral code
            
          
            $number_invited_customers = $this->getNumberOfFriends($registered_customer->getReferrerId());
    
            $amount = $this->getAmountReward($number_invited_customers);
          
            if ($amount > 0) {
                $comment = sprintf("Customer %s %s registered from invitation. Number %s. Giving %s credits",  $registered_customer->getId(), $registered_customer->getEmail(), $number_invited_customers, $amount);
                $this->giveCredits($inviter->getId(), $amount, $comment);
            }
         }
         
     } 
     
     /*
      * Get the number of friends by referral_id
      */
     public function getNumberOfFriends($referral_id) {
        $invited_customers =  Mage::getModel('customer/customer')->getCollection()
                              ->addAttributeToFilter('referrer_id', $referral_id)->load();
        
        return $invited_customers->count();
        
     }
     
     public function getAmountReward($number_of_friends) {
        
        $amount = 0;
        switch($number_of_friends) {
            case 6:
                $amount = 10;
                break;
            case 10: 
                $amount = 20;
                break;
            case 15:
                $amount = 40;
                break;
        } 
        return $amount;
        
     }
	 public function giveCredits($customer_id, $amount, $comment) {
	 
        if (!Mage::helper('customercredit')->isEnabled()) return false;                
        $customerCredit = Mage::getModel('customercredit/credit');
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $data['website_id']   = 1;
        $data['credit_value'] = Mage::helper('customercredit')->getCreditValue($customer_id, $data['website_id']);
        $data['comment']      = $comment;
        $data['value_change'] = $amount;
        $customer->setCustomerCreditData($data);
        $customer->save();
        if (!empty($amount)) {
            
           
            // no minus
            if ((floatval($data['credit_value']) + floatval($data['value_change'])) < 0 ) $data['value_change'] = floatval($data['credit_value'])*-1;
             
            $customerCredit->setData($data)->setCustomer($customer)->save();
            
            // if send email
            if (Mage::helper('customercredit')->isSendNotificationBalanceChanged()) {      
                   
                Mage::helper('customercredit')->sendNotificationBalanceChangedEmail($customer);
            }
            
        }
    }
}
