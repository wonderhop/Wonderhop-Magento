<?php 

$installer = $this;

$installer->startSetup();
$installer->run("

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
  `status` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

");

$installer->endSetup();


