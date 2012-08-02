<?php

$installer = $this;

$installer->startSetup();

$forms = array( 'adminhtml_customer' );
$attributes = array(
    'utm_source'              => 'Utm Source',
    'utm_campaign'            => 'Utm Campaign',
    'utm_medium'              => 'Utm Medium',
    'utm_content'             => 'Utm Content',
    'referral_code'           => 'Referral Generated Code',
    'referrer_id'             => 'Referrer Id',
    'succesfull_invites'      => 'Successfull Invites'
);
foreach($attributes as $attr => $label) {

        $installer->addAttribute( 'customer', $attr, array(
                'type'              => 'varchar',
                'input'             => 'text',
                'label'             => $label,
                'required'          => false,
                'visible'           => true,
                'user_defined'      => false,
        ));
        
        Mage::getSingleton( 'eav/config' )
            ->getAttribute( 'customer', $attr )
            ->setData( 'used_in_forms', $forms )
            ->save();
 
}

$installer->endSetup();

