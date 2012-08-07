<?php
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('invitation')} (
  `invitation_id` int(11) unsigned NOT NULL auto_increment,
  `customer_fk` bigint(11) NOT NULL,
  `template_id` int(11)  NOT NULL,    
  `invitation_send_date` datetime NOT NULL,
  `sent_to`  varchar(64),
  `registration_date` datetime,
  `registered` int(1),
  `purchased`  int(1),
  `invitee_customer_fk`bigint(11),
  `sent_to_facebook_id` BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`invitation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup(); 
