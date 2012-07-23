<?php
$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$category = Mage::getModel('catalog/category');
$category->setStoreId(0);
$root_category['name'] = 'Sales';
$root_category['path'] = '1/2';
$root_category['url_key'] = 'sales';
$root_category['display_mode'] = 'PRODUCTS';
$root_category['is_active'] = 1;
$category->addData($root_category);
$category->save();
 
$entity_type_id     = $installer->getEntityTypeId('catalog_category');
$attribute_set_id   = $installer->getDefaultAttributeSetId($entity_type_id);
$attribute_group_id = $installer->getDefaultAttributeGroupId($entity_type_id, $attribute_set_id);

// adding attribute group
$setup->addAttributeGroup('catalog_category', 'Default', 'Sale', 1000);
 

$installer->addAttribute('catalog_category', 'start_date', array(
	'type'         => 'datetime',
	'group'        => 'Sale',
	'label'        => 'Start Date',
	'input'        => 'date',
	'backend'      => 'eav/entity_attribute_backend_datetime',
	'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible'      => true,
	'required'     => false,
	'user_defined' => false,
));

$installer->addAttribute('catalog_category', 'end_date', array(
	'type'         => 'datetime',
	'group'        => 'Sale',
	'label'        => 'End Date',
	'input'        => 'date',
	'backend'      => 'eav/entity_attribute_backend_datetime',
	'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible'      => true,
	'required'     => false,
	'user_defined' => false,
));

$installer->addAttribute('catalog_category', 'sale_description', array(
	'type'         => 'text',
	'group'        => 'Sale',
	'label'        => 'Small sale description',
	'input'        => 'text',
	'visible'      => true,
	'required'     => false,
	'user_defined' => false,
	'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$installer->addAttribute('catalog_category', 'extra_image_first', array(
	'type'         => 'varchar',
	'group'        => 'Sale',
	'label'        => 'Additional Sale Image 1',
	'frontend_input' => 'image',
	'input'        => 'image',
	'visible'      => true,
	'required'     => false,
	'user_defined' => false,
	'backend'      =>'catalog/category_attribute_backend_image',
	'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));

$installer->addAttribute('catalog_category', 'extra_image_second', array(
	'type'         => 'varchar',
	'group'        => 'Sale',
	'label'        => 'Additional Sale Image 2',
	'frontend_input' => 'image',
	'input'        => 'image',
	'visible'      => true,
	'required'     => false,
	'user_defined' => false,
	'backend'      =>'catalog/category_attribute_backend_image',
	'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));

 
$installer->addAttributeToGroup(
	$entity_type_id,
	$attribute_set_id,
	$attribute_group_id,
	'display_in_showroom',
	'100'
);

$installer->endSetup();
