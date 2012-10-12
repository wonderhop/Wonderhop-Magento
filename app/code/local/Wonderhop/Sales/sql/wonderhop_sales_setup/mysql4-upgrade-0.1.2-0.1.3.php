<?php

$installer = $this;

$installer->startSetup();

$forms = array( 'adminhtml_customer' );
$attributes = array(
    'ad'              => 'Ad'
);
foreach($attributes as $attr => $label) {

        $installer->addAttribute( 'customer', $attr, array(
                'type'              => 'text',
                'label'             => $label,
                'required'          => false,
                'visible'           => false,
                'user_defined'      => false,
 
        ));
        
        Mage::getSingleton( 'eav/config' )
            ->getAttribute( 'customer', $attr )
            ->setData( 'used_in_forms', $forms )
            ->save();
 
}

$installer->endSetup();

