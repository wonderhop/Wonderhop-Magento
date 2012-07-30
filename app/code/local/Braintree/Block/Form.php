<?php

class Braintree_Block_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('braintree/form.phtml');
    }

    protected function _toHtml()
    {
        if ($this->getMethod()->getCode() != Mage::getSingleton('braintree/paymentMethod')->getCode()) {
            return null;
        }

        return parent::_toHtml();
    }

    public function setMethodInfo()
    {
        $payment = Mage::getSingleton('checkout/type_onepage')
            ->getQuote()
            ->getPayment();
        $this->setMethod($payment->getMethodInstance());

        return $this;
    }

}
