<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$version = '0.5.2';
Mage::log('Running Fooman Jirafe DB upgrade '.$version);

$installer = $this;
/* @var $installer Fooman_Jirafe_Model_Mysql4_Setup */

$installer->startSetup();
Mage::helper('foomanjirafe/setup')->runDbSchemaUpgrade($installer, $version);
$installer->endSetup();

//Run sync when finished with install/update
Mage::getModel('foomanjirafe/jirafe')->initialSync($version);
