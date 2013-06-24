<?php

class MagentoMasters_Supplier_Model_Emailsender {
    public function toOptionArray()
    {
        $senders =  Mage::getStoreConfig('trans_email');
        $values = array();
        foreach($senders as $sender){
            $values[] = array(
                'value' => $sender['name'] . "::". $sender['email'],
                'label' => $sender['name']
            );
        }
        return $values;
    }
}