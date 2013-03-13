<?php
$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('registerqueue')} add column registerqueue_referral_code varchar(255);
ALTER TABLE {$this->getTable('registerqueue')} add column registerqueue_referral_id varchar(255);
");

$installer->endSetup();

