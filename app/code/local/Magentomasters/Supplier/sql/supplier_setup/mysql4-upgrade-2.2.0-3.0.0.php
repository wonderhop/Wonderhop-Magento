<?php
$installer = $this;

$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'pdf_template', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_ftp_path', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'csv_delimeter', 'varchar(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'xml_csv', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'custom1', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'custom2', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'custom3', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'custom4', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'address1', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'address2', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'city', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'postalcode', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'country', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'state', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'phone', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'contact', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'company', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_enabled', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule', 'varchar(1000) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_dropship_date', 'datetime NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock_enabled', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock', 'varchar(1000) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock_date', 'datetime NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock_url', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock_type', 'int(11) NOT NULL'); 
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock_qty', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock_sku', 'varchar(255) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'schedule_import_stock_divider', 'varchar(11) NOT NULL');


$installer->getConnection()->addColumn($this->getTable('supplier_dropship_items'), 'price', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_dropship_items'), 'cost', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_dropship_items'), 'method', 'varchar(255) NOT NULL');


$installer->startSetup();
$installer->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable ("supplier_templates")}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `header` text COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `item` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

");

$installer->endSetup();