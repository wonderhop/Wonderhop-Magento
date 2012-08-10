<?php
/**
 * EYEMAGINE - The leading Magento Solution Partner
 *
 * @author     EYEMAGINE <magento@eyemaginetech.com>
 * @category   Eyemagine
 * @package    Eyemagine_Cloudsponge
 * @copyright  Copyright (c) 2003-2012 EyeMagine Technology, LLC (http://www.eyemaginetech.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html (GPL)
 */
 
$installer = $this;
$installer->startSetup();
$installer->run("
 
-- DROP TABLE IF EXISTS {$this->getTable('cloudsponge')};
CREATE TABLE {$this->getTable('cloudsponge')} (
  `cloudsponge_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`cloudsponge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
 
$installer->endSetup();