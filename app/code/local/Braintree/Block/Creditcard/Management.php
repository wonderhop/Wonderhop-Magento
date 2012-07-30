<?php

require_once('Braintree/lib/Braintree.php');

class Braintree_Block_Creditcard_Management extends Mage_Customer_Block_Account_Dashboard
{
    protected $braintree;

    function __construct()
    {
        parent::__construct();
        $this->setTemplate('braintree/creditcard/index.phtml');
        $this->braintree = Mage::getModel('braintree/paymentmethod');
    }


    function creditCard()
    {
        $result = Mage::registry('braintree_result');
        if (!empty($result))
        {
            $token = ($result->success) ? $result->creditCard->token : $result->params['paymentMethodToken'];
        }
        else
        {
            $token = Mage::app()->getRequest()->getParam('token');
        }
        return $this->braintree->storedCard($token);
    }

    function getPostParam($index, $default='')
    {
        $result = Mage::registry('braintree_result');
        if (!empty($result))
        {
            $indices = explode('.', $index);
            $value = $result->params;
            foreach($indices as $key)
            {
                if (is_array($value[$key]))
                {
                    $value = $value[$key];
                }
                else
                {
                    return $value[$key];
                }
            }

        }
        else
        {
            return $default;
        }
    }

    function errors()
    {
        $result = Mage::registry('braintree_result');
        if (!empty($result))
        {
            return Mage::helper('braintree/messages')->errors(explode("\n", $result->message));
        }
    }

    function hasErrors()
    {
        $result = Mage::registry('braintree_result');
        return !empty($result) && !$result->success;
    }

    function buildTrData($redirectUrl)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if($this->braintree->exists($customer->getId()))
        {
            return Braintree_TransparentRedirect::createCreditCardData(array(
                'redirectUrl' => $redirectUrl,
                'creditCard' => array('customerId' => $customer->getId())
            ));
        }
        else
        {
            $credit_card_billing = array();
            $billing = $customer->getDefaultBilling();
            if ($billing)
            {
                $address = Mage::getModel('customer/address')->load($billing);
                $credit_card_billing['billingAddress'] = $this->braintree->toBraintreeAddress($address);
            }
            return Braintree_TransparentRedirect::createCustomerData(array(
                'redirectUrl' => $redirectUrl,
                'customer' => array(
                    'id' => $customer->getId(),
                    'firstName' => $customer->getFirstname(),
                    'lastName' => $customer->getLastname(),
                    'company' => $customer->getCompany(),
                    'phone' => $customer->getTelephone(),
                    'fax' => $customer->getFax(),
                    'email' => $customer->getEmail(),
                    'creditCard' => $credit_card_billing
               )));
        }
    }

    function hasDefaultAddress()
    {
        $defaultAddress = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        return !is_null($defaultAddress);
    }
}
