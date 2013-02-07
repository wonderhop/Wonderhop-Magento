<?php
$installer = $this;
$installer->startSetup();

$forms = array('adminhtml_customer');
$attributes = array(
	'can_use_share_coupon' => 'Can use share coupon'
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

	Mage::getSingleton('eav/config')
	->getAttribute('customer', $attr)
	->setData('used_in_forms', $forms)
	->save();
}

$installer->endSetup();
?>
