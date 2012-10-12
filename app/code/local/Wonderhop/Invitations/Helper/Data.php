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
         try {
		error_log  ("Trying rewardme for $registered_customer_id");
            $this->rewardMe($registered_customer);
         } catch(Exception $e) {
            Mage::log("$e");
         }
         #inviter customers having this friend code
         $inviter = Mage::getModel('customer/customer')->getCollection()
                   ->addAttributeToFilter('referral_code', $registered_customer->getReferrerId())->getFirstItem();
         
         if (!$inviter) {
            Mage::throwException(sprintf("Customer with generated code %s doesn't exists invited customer id %d", $customer->getReferrerId(), $customer->getId()));
         }
                      
         if (!$registered_customer->getReferrerId()) {
            return;
         }
         
         if($this->giveInviteeEnabled() && 0) {
            $amount = $this->getInviteeAmount();
            $comment = sprintf("Customer registered from invitation. Receives %s credits", $amount);
            $this->giveCredits($registered_customer_id, $amount, $comment);
         }
         
         if($this->giveInviterEnabled()) {
            $amount = $this->getInviterAmount();
            
            #get number of already registered customers with this referral code
            
          
            $number_invited_customers = $this->getNumberOfFriends( $inviter);
    
            $amount = $this->getAmountReward($number_invited_customers);
          
            if ($amount > 0) {
                $comment = sprintf("Customer %s %s registered from invitation. Number %s. Giving %s credits",  $registered_customer->getId(), $registered_customer->getEmail(), $number_invited_customers, $amount);
                $this->giveCredits($inviter->getId(), $amount, $comment);
            }
         }
         
     } 
     
     public function rewardMe($registered_customer) {
        
        $existing_customers = array();
        
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $sql = "select email from subscribers where is_user = 1 and invited_by = 
                (select id from subscribers where email = '{$registered_customer->getEmail()}')";
                
        $read->query( $sql );

        foreach( $read->fetchAll( $sql ) as $result ) {
            $existing_customers[$result['email']] = 1;
        }
        
        $no = count($existing_customers);
	error_log ("rewardme " . $registered_customer->getEmail() . " has $no friends");
        if (!$no) {
            return;
        }
       
        $amount = 0;
        
        if($no >= 6) {
            $amount += 10;
        }
        
        if($no >= 10) {
            $amount += 10;
        }
        
        if($no >= 15) {
            $amount += 20;
        }
        
        if ($amount > 0) {
            $comment = sprintf("Customer %s %s registered with  %s friends. Giving %s credits",  $registered_customer->getId(), $registered_customer->getEmail(), $no, $amount);
            $this->giveCredits($registered_customer->getId(), $amount, $comment);
        }
         
     }
     
     /*
      * Get the number of friends by referral_id
      */
     public function getNumberOfFriends($user) {
        
        $invited_customers =  Mage::getModel('customer/customer')->getCollection()
                              ->addAttributeToFilter('referrer_id', $user->getReferralCode())->load();
        $existing_customers = array();
        
        foreach ($invited_customers as $customer) {
            $existing_customers[$customer->getEmail()] = 1;
        }
      
        $sql = "select email from subscribers where is_user = 1 and invited_by = 
                (select id from subscribers where email = '{$user->getEmail()}')";
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');        
        $read->query( $sql );

        foreach( $read->fetchAll( $sql ) as $result ) {
            $existing_customers[$result['email']] = 1;
        }
        

	error_log(print_r($existing_customers,true));
         
        return count($existing_customers);
        
        
     }
     
     public function getAmountReward($number_of_friends) {
        
        $amount = 0;
        switch($number_of_friends) {
            case 6:
                $amount = 10;
                break;
            case 10: 
                $amount = 10;
                break;
            case 15:
                $amount = 20;
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
