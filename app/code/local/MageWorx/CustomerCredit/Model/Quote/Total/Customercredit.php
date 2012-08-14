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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Customer Credit extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @author     MageWorx Dev Team
 */

class MageWorx_CustomerCredit_Model_Quote_Total_Customercredit extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct() {
        $this->setCode('customercredit');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address) {        
        if (Mage::app()->getStore()->isAdmin()) {
            $allItems = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getAllItems();
            $productIds = array();
            foreach ($allItems as $item) {
                $productIds[] = $item->getProductId();
            }
        } else {
            $productIds = Mage::getSingleton('checkout/cart')->getProductIds();            
        }
        
        if (count($productIds)==0) return $this;
        
        $addressType = Mage_Sales_Model_Quote_Address::TYPE_BILLING;
        foreach ($productIds as $productId) {
            $productTypeId = Mage::getModel('catalog/product')->load($productId)->getTypeId();
            if ($productTypeId!='downloadable' && $productTypeId!='virtual') {
                $addressType = Mage_Sales_Model_Quote_Address::TYPE_SHIPPING;
                break;
            }
        }
        
        //shipping or billing
        if (!Mage::helper('customercredit')->isEnabled() || $addressType!=$address->getAddressType()) return $this;

        
        $address->setCustomerCreditAmount(0);
        $address->setBaseCustomerCreditAmount(0);

        $session = Mage::getSingleton('checkout/session');
        $useInternalCredit = $session->getUseInternalCredit();
        $session->setUseInternalCredit(false);
        
        
        
        $paymentData = Mage::app()->getRequest()->getPost('payment');
        $orderData = Mage::app()->getRequest()->getPost('order');
        
                        
        if(Mage::getModel('checkout/cart')->getQuote()->getData('items_qty') == 0 && !Mage::getSingleton('adminhtml/session_quote')->getCustomerId()) {
            return $this;
        }
        
        $quote = $address->getQuote();
        if ($quote->getPayment()->getMethod()=='customercredit'
            || ($orderData && isset($orderData['payment_method']) && $orderData['payment_method'] == 'customercredit')
            || ($paymentData && isset($paymentData['use_internal_credit']) && $paymentData['use_internal_credit'] > 0)
            || ($useInternalCredit && Mage::getSingleton('customer/session')->getCustomerId() && !$paymentData)
            ) {
            //$session->setUseInternalCredit(true);
        } else {
            return $this;
        }                              
        
        $baseCredit = Mage::helper('customercredit')->getCreditValue($quote->getCustomerId(), Mage::app()->getStore($quote->getStoreId())->getWebsiteId());        
        if ($baseCredit==0) return $this;
        
        $credit = $quote->getStore()->convertPrice($baseCredit);
                
        
        $baseGrandTotal = floatval($address->getBaseGrandTotal());
        $grandTotal = floatval($address->getGrandTotal());
        
        
        
        $baseShipping = floatval($address->getBaseShippingAmount() - $address->getBaseShippingTaxAmount());
        $shipping = floatval($address->getShippingAmount() - $address->getShippingTaxAmount());
                
        $baseTax = floatval($address->getBaseTaxAmount());
        $tax = floatval($address->getTaxAmount());       
        
        
        if ($baseGrandTotal) $baseSubtotal = $baseGrandTotal - $baseShipping - $baseTax; else $baseSubtotal = floatval($address->getBaseSubtotalWithDiscount());
        if ($grandTotal) $subtotal = $grandTotal - $shipping - $tax; else $subtotal = floatval($address->getSubtotalWithDiscount());       
        
        $baseCreditLeft = 0;
        $creditLeft = 0;        
        $creditTotals = Mage::helper('customercredit')->getCreditTotals();        
        foreach ($creditTotals as $field) {
            switch ($field) {
                case 'subtotal':                            
                    $baseCreditLeft += $baseSubtotal;
                    $creditLeft += $subtotal;
                    break;
                case 'shipping':
                    $baseCreditLeft += $baseShipping;
                    $creditLeft += $shipping;                   
                    break;
                case 'tax':
                    $baseCreditLeft += $baseTax;
                    $creditLeft += $tax;
                    break;                       
            }
        }
        
       
        if (!$baseCreditLeft) return $this;
        
        
        // if authorizenet and orderspro_order_edit and credit => adjustment of GrandTotal
        if (Mage::app()->getStore()->isAdmin() && Mage::app()->getRequest()->getControllerName() == 'orderspro_order_edit' && $quote->getPayment()->getMethod() == 'authorizenet') {
            $orderIdPrev = Mage::getSingleton('adminhtml/sales_order_create')->getSession()->getOrderId();
            if ($orderIdPrev > 0) {
                $orderPrev = Mage::getModel('sales/order')->load($orderIdPrev);
                $paymentPrev = $orderPrev->getPayment();
                if ($paymentPrev->getMethod() == 'authorizenet' && $paymentPrev->getBaseAmountOrdered() > 0 && $address->getBaseGrandTotal() >= $paymentPrev->getBaseAmountOrdered()) {
                    $baseCreditLeft = $address->getBaseGrandTotal() - $paymentPrev->getBaseAmountOrdered();
                    $creditLeft = $address->getGrandTotal() - $paymentPrev->getAmountOrdered();
                }
            }
        }
        
        
        $isEnabledPartialPayment = Mage::helper('customercredit')->isEnabledPartialPayment();
        if ($baseCredit < $baseCreditLeft) {
            if ($isEnabledPartialPayment) {
                $baseCreditLeft = $baseCredit;
                $creditLeft = $credit;
            } else {
                return $this;
            }    
        }
        
        $address->setBaseCustomerCreditAmount($baseCreditLeft);
        $address->setCustomerCreditAmount($creditLeft);

        $address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseCreditLeft);
        $address->setGrandTotal($address->getGrandTotal() - $creditLeft); 
        
        
        $session->setUseInternalCredit(true);

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {   
        if (!Mage::helper('customercredit')->isEnabled()) return $this;        

        if ($address->getCustomerCreditAmount()>0) {
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('customercredit')->__('Internal Credit'),
                'value'=>-$address->getCustomerCreditAmount(),
            ));
        }
        return $this;
    }
}