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

class Fooman_Jirafe_Model_Mysql4_Event extends Mage_Core_Model_Mysql4_Abstract
{

    protected function _construct ()
    {
        $this->_init('foomanjirafe/event', 'id');
    }

    public function acquireAdvisoryLock($siteId)
    {
        $lock = sprintf('jirafe_events_%d', $siteId);
        return (bool)$this->_getWriteAdapter()->raw_fetchRow("SELECT GET_LOCK('{$lock}', 0) AS l", 'l');
    }

    public function releaseAdvisoryLock($siteId)
    {
        $lock = sprintf('jirafe_events_%d', $siteId);
        return (bool)$this->_getWriteAdapter()->raw_fetchRow("SELECT RELEASE_LOCK('{$lock}') AS l", 'l');
    }

    public function getLastVersionNumber($siteId)
    {
        //the below query seems to perform slightly better on high concurrency than
        //->from($this->getTable('foomanjirafe/event'), array(new Zend_Db_Expr('max(version) as version')))
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('foomanjirafe/event'),'version')
            ->order('version DESC')
            ->limit(1)
            ->where('site_id = ?', $siteId);

        $res = $this->_getReadAdapter()->fetchRow($select);
        return isset($res['version']) ? $res['version'] : 0;
    }


    protected function _beforeSave(Mage_Core_Model_Abstract $event)
    {
        //need to lock the table so that version is consecutive per store id
        //and no events are dropped
        $tableName = $this->getMainTable();
        $this->_getWriteAdapter()->raw_query("LOCK TABLES `{$tableName}` WRITE;");
        $lastEventNumberForSite = $this->getLastVersionNumber($event->getSiteId());
        $event->setVersion($lastEventNumberForSite + 1);
        if (Mage::helper('foomanjirafe')->isDebug()) {
            $event = array('v' => $event->getVersion(), 'a' => $event->getAction(), 'd' => json_decode($event->getEventData(), true));
            Mage::helper('foomanjirafe')->debugEvent(json_encode($event));
        }

        return $this;
    }


    protected function _afterSave(Mage_Core_Model_Abstract $event)
    {
        $this->_getWriteAdapter()->raw_query("UNLOCK TABLES;");
        return $this;
    }

}