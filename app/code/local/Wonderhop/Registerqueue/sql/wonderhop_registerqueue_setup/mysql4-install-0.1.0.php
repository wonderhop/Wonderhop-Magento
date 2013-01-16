<?php
$installer = $this;
$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('registerqueue')} (
	`registerqueue_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`registerqueue_email` varchar(255) DEFAULT NULL,
	`registerqueue_date` datetime NOT NULL,
	`registerqueue_invited_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`registerqueue_invited` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`registerqueue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup(); 
