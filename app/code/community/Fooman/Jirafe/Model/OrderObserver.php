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
 * @copyright   Copyright (c) 2012 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2012 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Model_OrderObserver
{

    /**
     * salesOrderSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     *
     * @see salesOrderSaveCommitAfter
     * @param type $observer
     */
    public function salesOrderSaveAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->salesOrderSaveCommitAfter($observer);
        }
    }

    /**
     * Save this event to the Event Table for later synchronisation with Jirafe
     *
     * @param $observer
     */
    public function salesOrderSaveCommitAfter ($observer)
    {
        Mage::helper('foomanjirafe')->debug('salesOrderSaveCommitAfter');
        Mage::getModel('foomanjirafe/event')->orderCreateOrUpdate($observer->getEvent()->getOrder());
    }

    public function salesOrderCreditmemoSaveBefore ($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if(!$creditmemo->getId()) {
            $creditmemo->setJirafeIsNew(1);
            $creditmemo->setJirafeExportStatus(1);
        }
    }

    /**
     * salesCreditmemoSaveCommitAfter is not available on Magento 1.3
     * provide the closest alternative
     *
     * @see salesOrderCreditmemoSaveCommitAfter
     * @param type $observer
     */
    public function salesOrderCreditmemoSaveAfter ($observer)
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            $this->salesOrderCreditmemoSaveCommitAfter($observer);
        }
    }

    /**
     * Save this event to the Event Table for later synchronisation with Jirafe
     *
     * @param $observer
     */
    public function salesOrderCreditmemoSaveCommitAfter ($observer)
    {
        Mage::helper('foomanjirafe')->debug('salesCreditmemoSaveCommitAfter');
        Mage::getModel('foomanjirafe/event')->creditmemoCreateOrUpdate($observer->getEvent()->getCreditmemo());
    }

}
