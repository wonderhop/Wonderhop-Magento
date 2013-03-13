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

class Fooman_Jirafe_Model_Event extends Mage_Core_Model_Abstract
{
    //TODO: these could move into the php-client
    const JIRAFE_ACTION_ORDER_CREATE    = 'orderCreate';
    const JIRAFE_ACTION_ORDER_UPDATE    = 'orderUpdate';
    const JIRAFE_ACTION_ORDER_IMPORT    = 'orderImport';
    const JIRAFE_ACTION_INVOICE_CREATE  = 'invoiceCreate';
    const JIRAFE_ACTION_INVOICE_UPDATE  = 'invoiceUpdate';
    const JIRAFE_ACTION_SHIPMENT_CREATE = 'shipmentCreate';
    const JIRAFE_ACTION_REFUND_CREATE   = 'refundCreate';
    const JIRAFE_ACTION_REFUND_IMPORT   = 'refundImport';
    const JIRAFE_ACTION_NOOP            = 'noop';

    const JIRAFE_ORDER_STATUS_NEW               = 'new';
    const JIRAFE_ORDER_STATUS_PAYMENT_PENDING   = 'pendingPayment';
    const JIRAFE_ORDER_STATUS_PROCESSING        = 'processing';
    const JIRAFE_ORDER_STATUS_COMPLETE          = 'complete';
    const JIRAFE_ORDER_STATUS_CLOSED            = 'closed';
    const JIRAFE_ORDER_STATUS_CANCELLED         = 'canceled';
    const JIRAFE_ORDER_STATUS_HELD              = 'held';
    const JIRAFE_ORDER_STATUS_PAYMENT_REVIEW    = 'paymentReview';
    const JIRAFE_ORDER_STATUS_UNKNOWN           = 'unknown';

    protected $_eventPrefix = 'foomanjirafe_event';
    protected $_eventObject = 'jirafeevent';

    protected function _construct ()
    {
        $this->_init('foomanjirafe/event');
    }

    protected function _beforeSave()
    {
        $this->setGeneratedByJirafeVersion((string) Mage::getConfig()->getModuleConfig('Fooman_Jirafe')->version);
        parent::_beforeSave();
    }

    /**
     * there is no afterCommitCallback on earlier
     * versions, use the closest alternative
     */
    protected function _afterSave()
    {
        if (version_compare(Mage::getVersion(), '1.4.0.0', '<')) {
            if (!$this->getNoCMB()) {
                //ping Jirafe
                Mage::getSingleton('foomanjirafe/jirafe')->sendCMB($this->getSiteId());
            }
            return parent::_afterSave();
        }
    }

    public function afterCommitCallback()
    {
        if (!$this->getNoCMB()) {
            //ping Jirafe
            Mage::getSingleton('foomanjirafe/jirafe')->sendCMB($this->getSiteId());
        }
        return parent::afterCommitCallback();
    }

    protected function _getEventDataFromOrder($order)
    {
        $order = $order->load($order->getId());
        return array(
            'orderId'           => $order->getIncrementId(),
            'status'            => $this->_getOrderStatus($order),
            'customerHash'      => Mage::helper('foomanjirafe')->getCustomerHash($order->getCustomerEmail()),
            'visitorId'         => $this->_getJirafeVisitorId($order),
            'recovery_visit_id' => $order->getJirafeOrigVisitorId(),
            'time'              => strtotime($order->getCreatedAt()),
            'grandTotal'        => Mage::helper('foomanjirafe')->formatAmount($order->getBaseGrandTotal()),
            'subTotal'          => Mage::helper('foomanjirafe')->formatAmount($order->getBaseSubtotal()),
            'taxAmount'         => Mage::helper('foomanjirafe')->formatAmount($order->getBaseTaxAmount()),
            'shippingAmount'    => Mage::helper('foomanjirafe')->formatAmount($order->getBaseShippingAmount()),
            'discountAmount'    => $this->_formatDiscountAmount($order->getBaseDiscountAmount()),
            'items'             => $this->_getItems($order)
        );
    }

    protected function _getEventDataFromCreditMemo($creditmemo)
    {
        $creditmemo = $creditmemo->load($creditmemo->getId());
        return array(
                'refundId'                  => $creditmemo->getIncrementId(),
                'orderId'                   => $creditmemo->getOrder()->getIncrementId(),
                'time'                      => strtotime($creditmemo->getCreatedAt()),
                'grandTotal'                => Mage::helper('foomanjirafe')->formatAmount($creditmemo->getBaseGrandTotal()),
                'subTotal'                  => Mage::helper('foomanjirafe')->formatAmount($creditmemo->getBaseSubtotal()),
                'taxAmount'                 => Mage::helper('foomanjirafe')->formatAmount($creditmemo->getBaseTaxAmount()),
                'shippingAmount'            => Mage::helper('foomanjirafe')->formatAmount($creditmemo->getBaseShippingAmount()),
                'discountAmount'            => $this->_formatDiscountAmount($creditmemo->getBaseDiscountAmount()),
                'items'                     => $this->_getItems($creditmemo)
            );
    }

    public function orderCreateOrUpdate($order)
    {
        //getJirafeIsNew == 1 just placed order
        //getJirafeIsNew == 2 order saved as part of historical event creation = do nothing
        if ($order->getJirafeIsNew() == 1) {
            if (!$this->getNoCMB()) {
                //ping Jirafe for this new order - the call back creates the historical event orderCreate for this order
                Mage::getSingleton('foomanjirafe/jirafe')->sendCMB(Mage::helper('foomanjirafe')->getStoreConfig('site_id', $order->getStoreId()));
            }
        } elseif ($order->getJirafeIsNew() != 2) {
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_UPDATE);
            $eventData = $this->_getEventDataFromOrder($order);
            $this->setSiteId(Mage::helper('foomanjirafe')->getStoreConfig('site_id', $order->getStoreId()));
            $this->setEventData(json_encode($eventData));
            try {
                $this->save();
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('foomanjirafe')->debug($e->getMessage());
            }
        }
    }

    public function creditmemoCreateOrUpdate($creditmemo)
    {
        if ($creditmemo->getJirafeIsNew() == 1) {
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_REFUND_CREATE);
            $eventData = $this->_getEventDataFromCreditMemo($creditmemo);
            $this->setSiteId(Mage::helper('foomanjirafe')->getStoreConfig('site_id', $creditmemo->getStoreId()));
            $this->setEventData(json_encode($eventData));
            try {
                $this->save();
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('foomanjirafe')->debug($e->getMessage());
            }
            $creditmemo->setJirafeIsNew(2);
        }
    }

    public function orderImportCreate($siteId, $orders)
    {
        $eventData = array('orders' => array());
        foreach ($orders as $order) {
            Mage::helper('foomanjirafe')->debug('Adding order '.$order->getIncrementId().' to orderImport batch');
            $eventData['orders'][] = $this->_getEventDataFromOrder($order);
        }

        try {
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_ORDER_IMPORT);
            $this->setSiteId($siteId);

            $json = json_encode($eventData);
            while (strlen($json) >= 65535) {
                // Too big! Remove one entry and retry
                array_pop($eventData['orders']);
                array_pop($orders);
                $json = json_encode($eventData);
            }
            if (empty($json)) {
                Mage::throwException('Empty Jirafe order import event data.');
            }
            $this->setNoCMB(1)->setEventData($json);
            $this->save();

            foreach ($orders as $order) {
                $order->setJirafeIsNew(2)->setJirafeExportStatus(1)->save();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('foomanjirafe')->debug($e->getMessage());
        }

    }

    public function refundImportCreate($siteId, $refunds)
    {
        $eventData = array('refunds' => array());
        foreach ($refunds as $refund) {
            Mage::helper('foomanjirafe')->debug('Adding refund '.$refund->getIncrementId().' to refundImport batch');
            $eventData['refunds'][] = $this->_getEventDataFromCreditMemo($refund);
        }

        try {
            $this->setAction(Fooman_Jirafe_Model_Event::JIRAFE_ACTION_REFUND_IMPORT);
            $this->setSiteId($siteId);

            $json = json_encode($eventData);
            while (strlen($json) >= 65535) {
                // Too big! Remove one entry and retry
                array_pop($eventData['refunds']);
                array_pop($refunds);
                $json = json_encode($eventData);
            }
            if (empty($json)) {
                Mage::throwException('Empty Jirafe refund import event data.');
            }
            $this->setNoCMB(1)->setEventData($json);
            $this->save();

            foreach ($refunds as $refund) {
                $refund->setJirafeIsNew(2)->setJirafeExportStatus(1)->save();
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('foomanjirafe')->debug($e->getMessage());
        }

    }

    protected function _getJirafeVisitorId($order)
    {
        if ($order->getJirafePlacedFromFrontend()) {
            $visitorId = $order->getJirafeVisitorId();
        } else {
            $visitorId = null;
        }
        unset($order);
        return $visitorId;
    }

    protected function _formatDiscountAmount($discountAmount)
    {
        return Mage::helper('foomanjirafe')->formatAmount(abs($discountAmount));
    }

    protected function _getItems($salesObject)
    {
        $returnArray = array();
        $isOrder = ($salesObject instanceof Mage_Sales_Model_Order);
        foreach ($salesObject->getAllItems() as $item)
        {
            if($item){
                if ($isOrder) {
                    $orderItem = $item;
                } else {
                    $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                }
                if ($orderItem->getParentItemId()) {
                    // Skip sub-items
                    continue;
                }

                $product = Mage::getModel('catalog/product')->load($item->getProductId());

                $itemPrice = $orderItem->getBasePrice();
                // This is inconsistent behaviour from Magento
                // base_price should be item price in base currency
                // TODO: add test so we don't get caught out when this is fixed in a future release
                if(!$itemPrice || $itemPrice < 0.00001) {
                    $itemPrice = $orderItem->getPrice();
                }

                $currentItem = array(
                    'price' => Mage::helper('foomanjirafe')->formatAmount($itemPrice),
                    'quantity' => $isOrder ? $item->getQtyOrdered() : $item->getQty()
                );

                //a product might have been deleted - use item information
                if ($product->getId()) {
                    $currentItem['sku'] = Mage::helper('foomanjirafe')->toUTF8($product->getData('sku'));
                    $currentItem['name'] = Mage::helper('foomanjirafe')->toUTF8($product->getName());
                    $currentItem['category'] = Mage::helper('foomanjirafe')->toUTF8(Mage::helper('foomanjirafe')->getCategory($product));
                } else {
                    $currentItem['sku'] = Mage::helper('foomanjirafe')->toUTF8($item->getData('sku'));
                    $currentItem['name'] = Mage::helper('foomanjirafe')->toUTF8($item->getName());
                }
                $returnArray[] = $currentItem;
            }
        }
        return $returnArray;
    }

    protected function _getOrderStatus($order)
    {
        $state = $order->getState();
        unset($order);
        switch ($state) {
            case Mage_Sales_Model_Order::STATE_NEW:
                $status = self::JIRAFE_ORDER_STATUS_NEW;
                break;
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
                $status = self::JIRAFE_ORDER_STATUS_PAYMENT_PENDING;
                break;
            case Mage_Sales_Model_Order::STATE_PROCESSING:
                $status = self::JIRAFE_ORDER_STATUS_PROCESSING;
                break;
            case Mage_Sales_Model_Order::STATE_COMPLETE:
                $status = self::JIRAFE_ORDER_STATUS_COMPLETE;
                break;
            case Mage_Sales_Model_Order::STATE_CLOSED:
                $status = self::JIRAFE_ORDER_STATUS_CLOSED;
                break;
            case Mage_Sales_Model_Order::STATE_CANCELED:
                $status = self::JIRAFE_ORDER_STATUS_CANCELLED;
                break;
            case Mage_Sales_Model_Order::STATE_HOLDED:
                $status = self::JIRAFE_ORDER_STATUS_HELD;
                break;
            case 'payment_review': //Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW
                $status = self::JIRAFE_ORDER_STATUS_PAYMENT_REVIEW;
                break;
            default:
                $status = self::JIRAFE_ORDER_STATUS_UNKNOWN;
                break;
        }
        return $status;
    }

}
