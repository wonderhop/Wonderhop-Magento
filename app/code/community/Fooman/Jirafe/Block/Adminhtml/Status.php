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

class Fooman_Jirafe_Block_Adminhtml_Status extends Mage_Adminhtml_Block_Template
{

    public function __construct ()
    {
        $this->setTemplate('fooman/jirafe/status.phtml');
    }

    public function isConfigured ()
    {
        return Mage::helper('foomanjirafe')->isConfigured();
    }

    public function isOk ()
    {
        return Mage::helper('foomanjirafe')->isOk();
    }
    
    public function noSync ()
    {
        return Mage::helper('foomanjirafe')->noSync();
    }

    public function getStatus ()
    {
        return Mage::helper('foomanjirafe')->getStatus();
    }

    public function getStatusMessage ()
    {
        return Mage::helper('foomanjirafe')->getStoreConfig('last_status_message');
    }

    public function getSyncUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/jirafe/sync');
    }

    public function getReportUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/jirafe/report');
    }
    
    public function getResetUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/jirafe/reset');
    }

    public function getJirafeConfigData()
    {
        $conn = Mage::getSingleton('core/resource');
        $read = $conn->getConnection('core_read');
        return $read->fetchAll("SELECT * FROM `{$conn->getTableName('core_config_data')}` WHERE path like 'foomanjirafe%'");
    }

    public function getJirafeInstalledVersion()
    {
        $conn = Mage::getSingleton('core/resource');
        $read = $conn->getConnection('core_read');
        return $read->fetchOne("SELECT version FROM `{$conn->getTableName('core_resource')}` WHERE code like 'foomanjirafe_setup'");
    }

    public function getOrderSyncStatus()
    {
        return Mage::getModel('foomanjirafe/jirafe')->getOrderSyncStatus();
    }

    public function isDebug()
    {
        return Mage::helper('foomanjirafe')->isDebug();
    }

    public function isConfig()
    {
        return Mage::app()->getRequest()->getParam('section') == 'foomanjirafe';
    }

}