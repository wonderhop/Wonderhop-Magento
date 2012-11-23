<?php

class Wonderhop_Generic_Model_Data extends Mage_Core_Model_Abstract {
    
    
    public function addMarketingData($customer, $save = false, $DATA = NULL)
    {
        if ( ! is_object($customer)) {
            error_log(__FUNCTION__ , ' :: W ::: Supplied $customer is not an object');
            return;
        }
        if ( ! $DATA) $DATA = $_COOKIE;
        if ($DATA instanceof Varien_Object) $DATA = $DATA->getData();
        if (isset($DATA['wonderhop_r'])) {
            $customer->setData('referrer_id', $DATA['wonderhop_r']);
        }
        if (isset($DATA['wonderhop_a'])) {
            $customer->setData('ad', $DATA['wonderhop_a']);
        }
        if ($save) {
            $customer->save();
        }
    }
    
    
    public function oscRegisterCustomerData($customer)
    {
        if ( ! $customer->getId()) {
            error_log(__FUNCTION__ . ' ::: W ::: trying to set data before customer was created');
            return;
        }
        if ( ! $customer->getReferralCode()) {
            $this->addReferralCode($customer);
            $customer->save();
        }
        if ( ! $customer->getAd() and ! $customer->getReferrerId()) {
            $this->addMarketingData($customer, true);
        }
    }
    
    
    public function addReferralCode($customer)
    {
        if ( ! is_object($customer)) {
            error_log(__FUNCTION__ , ' :: W ::: Supplied $customer is not an object');
            return;
        }
        $customer->setReferralCode($this->newReferralCode());
    }
    
    public function newReferralCode()
    {
        $code = $this->generateReferralCode();
        while ( count( Mage::getModel('customer/customer')->getCollection()
                            ->addAttributeToFilter('referral_code', $code)->load()
                        ) > 0
        ) {
            $code = $this->generateReferralCode();  
        }
        return $code;
    }
    
    
     public function generateReferralCode($len = 6)
     {
        $hex = md5("referral" . uniqid("", true));
        $pack = pack('H*', $hex);
        $tmp =  base64_encode($pack);
        $uid = preg_replace("#(*UTF8)[^A-Za-z0-9]#", "", $tmp);
        $len = max(4, min(128, $len));
        while (strlen($uid) < $len)
            $uid .= gen_uuid(22);
        return substr($uid, 0, $len);
    }
    
}
