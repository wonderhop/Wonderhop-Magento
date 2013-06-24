<?php 

$installer = $this;

$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'username', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'pdf_enabled', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_enabled', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_type', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_ftp', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_ftp_type', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_ftp_host', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_ftp_port', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_ftp_user', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_ftp_password', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_template', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'shipping_cost_free', 'varchar(11)');

$installer->getConnection()->addColumn($this->getTable('supplier_dropship_items'), 'price', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_dropship_items'), 'cost', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_dropship_items'), 'method', 'varchar(255) NOT NULL');