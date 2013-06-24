<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable ("supplier_users")}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `surname` varchar(255) DEFAULT NULL,
  `email1` varchar(255) DEFAULT NULL,
  `email2` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '999999',
  `xml_name` varchar(255) DEFAULT NULL,
  `email_enabled` int(11) NOT NULL,
  `email_template` int(11) NOT NULL,
  `pdf_enabled` int(11) NOT NULL,
  `pdf_template` int(11) NOT NULL,
  `xml_enabled` int(11) NOT NULL,
  `xml_type` int(11) NOT NULL,
  `xml_ftp` int(11) NOT NULL,
  `xml_ftp_type` int(11) NOT NULL,
  `xml_ftp_host` varchar(255) NOT NULL,
  `xml_ftp_path` varchar(255) NOT NULL,
  `xml_ftp_port` int(11) NOT NULL,
  `xml_ftp_user` varchar(255) NOT NULL,
  `xml_ftp_password` varchar(255) NOT NULL,
  `xml_template` int(11) NOT NULL,
  `csv_delimeter` varchar(11) NOT NULL,
  `xml_csv` int(111) NOT NULL,
  `show_custom_attr` int(11) NOT NULL,
  `shipping_cost` varchar(11) NOT NULL,
  `shipping_cost_free` varchar(11) NOT NULL,
  `attributes_show` int(11) NOT NULL,
  `attributes` text NOT NULL,
  `email_header` text NOT NULL,
  `email_message` text NOT NULL,
  `pdf_header` text NOT NULL,
  `pdf_message` text NOT NULL,
  `custom1` text NOT NULL,
  `custom2` text NOT NULL,
  `custom3` text NOT NULL,
  `custom4` text NOT NULL,
  `address1` text NOT NULL,
  `address2` text NOT NULL,
  `city` text NOT NULL,
  `postalcode` text NOT NULL,
  `country` text NOT NULL,
  `state` text NOT NULL,
  `phone` text NOT NULL,
  `contact` text NOT NULL,
  `company` text NOT NULL,
  `schedule_enabled` int(11) NOT NULL DEFAULT '1',
  `schedule` varchar(1000) NOT NULL,
  `schedule_dropship_date` datetime NOT NULL,
  `schedule_import_stock_enabled` int(11) NOT NULL,
  `schedule_import_stock` varchar(1000) NOT NULL,
  `schedule_import_stock_date` datetime NOT NULL,
  `schedule_import_stock_url` varchar(255) NOT NULL,
  `schedule_import_stock_type` int(11) NOT NULL,
  `schedule_import_stock_qty` varchar(255) NOT NULL,
  `schedule_import_stock_sku` varchar(255) NOT NULL,
  `schedule_import_stock_divider` varchar(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable ("supplier_dropship_items")}`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dropship_id` int(255) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_number` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `supplier_name` text NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` text NOT NULL,
  `sku` varchar(255) NOT NULL,
  `qty` varchar(255) NOT NULL,
  `cost` decimal(12,4) NOT NULL,
  `price` decimal(12,4) NOT NULL,
  `status` int(11) NOT NULL,
  `method` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable ("supplier_templates")}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `header` text COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `item` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

");

$installer->endSetup();