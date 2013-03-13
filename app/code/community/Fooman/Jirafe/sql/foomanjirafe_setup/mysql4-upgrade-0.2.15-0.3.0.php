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
$version = '0.3.0';
Mage::log('Running Fooman Jirafe DB upgrade '.$version);

$time = Mage::getSingleton('core/date')->gmtTimestamp();
Mage::getModel('core/config_data')
        ->setPath(Fooman_Jirafe_Helper_Data::XML_PATH_FOOMANJIRAFE_SETTINGS . 'installtime')
        ->setValue($time)
        ->save();

//Run sync when finished with install/update
Mage::getModel('foomanjirafe/jirafe')->initialSync($version);
