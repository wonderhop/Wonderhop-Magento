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
        //if ($distinctId = $this->getTrackingDistinctId()) {
        //    $customer->setDistinctId($distinctId);
        //    $customer->save();
        //}
    }
    
    public function getTrackingDistinctId()
    {
        $distinctId = NULL;
        if ($mpkey = ((string)Mage::getConfig()->getNode('localconf/analytics/mixpanel/key'))) {
            $mpSessName = "mp_{$mpkey}_mixpanel";
            if (isset($_COOKIE[$mpSessName]) and $_COOKIE[$mpSessName]) {
                $mp = NULL;
                try { $mp = json_decode($_COOKIE[$mpSessName]); } catch(Exception $e) {};
                if (is_object($mp) and isset($mp->distinct_id) and preg_match('/^[a-f0-9\-]{10,}$/i', $mp->distinct_id)) {
                    $distinctId = $mp->distinct_id;
                }
            }
        }
        return $distinctId;
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
    
    
    public function isJustRegisteredCustomer($clear = true)
    {
        $session = Mage::getSingleton('customer/session');
        $core = Mage::getSingleton('core/session');
        $registry = Mage::registry('is_just_registered_customer');
        if ( ! is_null($registry)) {
            if ($clear) $core->unsCustomerRegistered();
            return $registry;
        }
        $is = (int)(bool)($session->isLoggedIn() and $core->getCustomerRegistered());
        Mage::register('is_just_registered_customer', $is);
        if ($clear) $core->unsCustomerRegistered();
        return $is;
    }
    
    public static function buyGiftcardSuccess()
    {
        $order_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $session = Mage::getSingleton('customer/session');
        $ammount = intval($session->getGiftcardAmmount());
        $generate = array(
            'is_new' => 1 ,
            'credit' => $ammount ,
            'website_id' => Mage::app()->getWebsite()->getId() ,
            'is_active' => 1,
            'from_date' => NULL ,
            'to_date' => NULL ,
            'generate' => array(
                'qty' => 1 ,
                'code_length' => 16 ,
                'group_length' => 4 ,
                'group_separator' => '-',
                'code_format' => 'alphanum',
            ),
        );
        $codeModel = Mage::getModel('customercredit/code');
        $codeModel->setData($generate);
        $codeModel->generate();
        $code = $codeModel->getCode();
        $template_id = (string)Mage::getConfig()->getNode('localconf/giftcard_template_id');
        if ( ! $template_id) { 
            error_log('NO gidtcard template id to send code : '.$code.'  to '.$session->getRecipientEmail());
            return;
        }
        $data = new Varien_Object;
        $data->setData(array(
            'code' => $code,
        ));
        $vars = array( 'data' => $data );
        // Set sender/recipient information
        $sender = array('name'  => 'Curio Road', 'email' => 'hello@curioroad.com');
        $recipient_email = $session->getRecipientEmail();
        $recipient_name = $session->getGiftToText();
        // Get Store ID
        $store_id = Mage::app()->getStore()->getId();
        $translate  = Mage::getSingleton('core/translate');
        // Send Transactional Email
        $tr_email = Mage::getModel('core/email_template')
            ->sendTransactional($template_id, $sender, $recipient_email, $recipient_name, $vars, $store_id);
        $translate->setTranslateInline(true);
    }
    
    
}
