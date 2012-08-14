<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @copyright  Copyright (c) 2010 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */
 
/**
 * Customer Credit extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_CustomerCredit_Model_Mysql4_Credit extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('customercredit/credit', 'credit_id');
    }

    public function loadByCustomerAndWebsite($object, $customerId, $websiteId) {
        $read = $this->_getReadAdapter();
        if ($read) {

            if ($websiteId) {
                $select = $read->select()
                        ->from($this->getMainTable())
                        ->where('customer_id = ?', $customerId)
                        ->where('website_id = ?', $websiteId)
                        ->limit(1);
            } else {
                $select = $read->select()
                        ->from($this->getMainTable())
                        ->where('customer_id = ?', $customerId)
                        ->limit(1);
            }

            $data = $read->fetchRow($select);
            if ($data) {
                $object->addData($data);
            }
        }
    }
}