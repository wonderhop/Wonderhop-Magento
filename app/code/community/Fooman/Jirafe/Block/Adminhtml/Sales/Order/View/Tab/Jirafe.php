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

class Fooman_Jirafe_Block_Adminhtml_Sales_Order_View_Tab_Jirafe
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('fooman/jirafe/order/view/tab/analytics.phtml');
    }
    public function getTabLabel()
    {
        return Mage::helper('foomanjirafe')->__('Jirafe Analytics');
    }
    
    public function getTabTitle()
    {
        return Mage::helper('foomanjirafe')->__('Jirafe Analytics');
    }
    
    public function getReferrerData ()
    {
        $order = $this->getOrder();
        if ($order->getJirafeAttributionData()) {
            $returnArray = array();
            $refData = json_decode($order->getJirafeAttributionData(), true);
            $returnArray[] = array('label' => Mage::helper('foomanjirafe')->__('Campaign Name'), 'data' => $refData[0]);
            $returnArray[] = array('label' => Mage::helper('foomanjirafe')->__('Campaign Keyword'), 'data' => $refData[1]);
            $returnArray[] = array('label' => Mage::helper('foomanjirafe')->__('Timestamp'), 'data' => $this->formatDate(date('Y-m-d H:i:s',$refData[2]), 'medium', true));
            $returnArray[] = array('label' => Mage::helper('foomanjirafe')->__('Referrer Url'), 'data' => $refData[3]);
        } else {
            $returnArray[] = array('label' => '', 'data' => Mage::helper('foomanjirafe')->__('No Referrer Information available'));
        }
        return $returnArray;
    }
    
    public function getJirafeExportStatus ()
    {
        $order = $this->getOrder();
        if($order->getId()) {
            return $order->getJirafeExportStatus();
        } else {
            return '';
        }
    }
}