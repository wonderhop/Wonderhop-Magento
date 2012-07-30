<?php

require_once('Braintree/lib/Braintree.php');

class Braintree_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{
    protected $_formBlockType = 'braintree/form';
    protected $_infoBlockType = 'payment/info';

    protected $_code = 'braintree';
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc               = false;
    protected $_merchantAccountId       = '';
    protected $_useVault                = false;

    public function __construct()
    {
        parent::__construct();
        if ($this->getConfigData('active') == 1)
        {
            Braintree_Configuration::environment($this->getConfigData('environment'));
            Braintree_Configuration::merchantId($this->getConfigData('merchant_id'));
            Braintree_Configuration::publicKey($this->getConfigData('public_key'));
            Braintree_Configuration::privateKey($this->getConfigData('private_key'));
            $this->_merchantAccountId = $this->getConfigData('merchant_account_id');
            $this->_useVault = $this->getConfigData('use_vault');
        }
    }

    public function validate()
    {
        return true;
    }

    public function currentCustomerStoredCards ()
    {
        if (Mage::app()->isInstalled() && $this->useVault() && Mage::getSingleton('customer/session')->isLoggedIn())
        {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            try
            {
                return Braintree_Customer::find($customerId)->creditCards;
            }
            catch (Braintree_Exception $e)
            {
                Mage::logException($e);
                return array();
            }
        }
        else
        {
            return array();
        }
    }

    public function storedCard($token)
    {
        try {
            return Braintree_CreditCard::find($token);
        }
        catch (Braintree_Exception $e) {
            Mage::logException($e);
        }
    }

    public function authorize (Varien_Object $payment, $amount)
    {
        $this->_authorize($payment, $amount, false);
    }

    private function _authorize (Varien_Object $payment, $amount, $capture)
    {
        try
        {

            $order = $payment->getOrder();
            $customerId = $order->getCustomerId();
            $orderId = $order->getIncrementId();
            $billing = $order->getBillingAddress();
            $shipping = $order->getShippingAddress();

            $transaction_params = array(
                'orderId' => $orderId,
                'amount' => $amount,
                'customer' => array(
                    'firstName' => $billing->getFirstname(),
                    'lastName'  => $billing->getLastname(),
                    'company'   => $billing->getCompany(),
                    'phone'     => $billing->getTelephone(),
                    'fax'       => $billing->getFax(),
                    'email'     => $order->getCustomerEmail(),
                )
            );

            if ($customerId)
            {
                $transaction_params['options']['storeInVault'] = $this->storeInVault();

                if ($this->exists($customerId))
                {
                    $transaction_params['customerId'] = $customerId;
                    unset($transaction_params['customer']);
                }
                else
                {
                    $transaction_params['customer']['id'] = $customerId;
                }
            }

            if ($capture)
            {
                $transaction_params['options']['submitForSettlement'] = true;
            }


            if ($this->_merchantAccountId)
                $transaction_params['merchantAccountId'] = $this->_merchantAccountId;

            if (isset($_POST['payment']['cc_token']) && $_POST['payment']['cc_token'])
            {
                $transaction_params['paymentMethodToken'] = $_POST['payment']['cc_token'];
                $transaction_params['customerId'] = $customerId;
            }
            else
            {
                $transaction_params['creditCard'] = array(
                    'cardholderName' => $billing->getFirstname() . ' ' . $billing->getLastname(),
                    'number' => $payment->getCcNumber(),
                    'cvv' => $payment->getCcCid(),
                    'expirationDate' => $payment->getCcExpMonth() . '/' . $payment->getCcExpYear()
                );
                $transaction_params['billing'] = $this->toBraintreeAddress($billing);
                $transaction_params['shipping'] = $this->toBraintreeAddress($shipping);

            }

            $result = Braintree_Transaction::sale($transaction_params);

            if ($result->success)
            {
                $this->setStore($payment->getOrder()->getStoreId());
                $payment->setStatus(self::STATUS_APPROVED)
                    ->setCcTransId($result->transaction->id)
                    ->setLastTransId($result->transaction->id)
                    ->setTransactionId($result->transaction->id)
                    ->setIsTransactionClosed(0)
                    ->setAmount($amount);
            }
            else if (isset($result->transaction) && $result->transaction->status == 'gateway_rejected')
            {
                Mage::throwException('gateway rejected');
            }
            else
            {
                Mage::throwException($result->message);
            }
        } catch (Exception $e) {
            Mage::throwException(sprintf('Transaction declined: %s', $e->getMessage()));
        }
        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        try
        {
            if ($payment->getCcTransId())
            {
                $result = Braintree_Transaction::submitForSettlement($payment->getCcTransId(), $amount);
                if ($result->success)
                {
                    $payment->setIsTransactionClosed(0);
                }
                else
                {
                    Mage::throwException($result->message);
                }
            }
            else
            {
                $this->_authorize($payment, $amount, true);
            }
        }
        catch (Exception $e) {
            Mage::throwException(sprintf('There was an error capturing the transaction. (%s)', $e->getMessage()));
        }
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        try
        {
            $transaction = Braintree_Transaction::find($payment->getCcTransId());
            $result = $transaction->status === Braintree_Transaction::SETTLED
                ? Braintree_Transaction::refund($payment->getCcTransId(), $amount)
                : Braintree_Transaction::void($payment->getCcTransId());
            if ($result->success)
            {
                $payment->setIsTransactionClosed(1);
            }
            else
            {
                Mage::throwException($result->message);
            }
        }
        catch (Exception $e) {
            Mage::throwException(sprintf('There was an error refunding the transaction. (%s)', $e->getMessage()));
        }
        return $this;
    }

    public function void(Varien_Object $payment)
    {
        try
        {
            $result = Braintree_Transaction::void($payment->getCcTransId());

            if ($result->success)
            {
                $payment->setIsTransactionClosed(1);
            }
            else
            {
                Mage::throwException($result->message);
            }
        }
        catch (Exception $e)
        {
            Mage::throwException(sprintf('There was an error voiding the transaction. (%s)', $e->getMessage()));
        }
        return $this;
    }

    public function storeInVault()
    {
        return $this->_useVault && isset($_POST['payment']['store_in_vault']) && $_POST['payment']['store_in_vault'];
    }

    public function deleteCustomer($customerID)
    {
        try {
            Braintree_Customer::delete($customerID);
        } catch (Braintree_Exception $e) {
            Mage::logException($e);
        }
    }

    public function confirm()
    {
        try
        {
            return Braintree_TransparentRedirect::confirm($_SERVER['QUERY_STRING']);
        }
        catch (Braintree_Exception $e)
        {
            Mage::logException($e);
            $result = stdClass;
            $result->success = false;
            return $result;
        }
    }

    public function deleteCard($token)
    {
        try
        {
            return Braintree_CreditCard::delete($token);
        }
        catch (Braintree_Exception $e)
        {
            Mage::logException($e);
            return false;
        }
    }

    public function exists($customerId)
    {
        try
        {
            $customer = Braintree_Customer::find($customerId);
            return true;
        }
        catch (Braintree_Exception $e)
        {
            Mage::logException($e);
            return false;
        }
    }

    public function saveAddress($braintree_result)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $address = Mage::getModel('customer/address');
        $customAddress = array();

        if(isset($braintree_result->token))
        {
            $customAddress = $this->toMageAddress($braintree_result->creditCard->billingAddress);
        }
        else
        {
            $customAddress = $this->toMageAddress($braintree_result->customer->creditCards[0]->billingAddress);
        }

        $address->setData($customAddress)->setCustomerID($customer->getId())->setIsDefaultBilling(1)->setSaveInAddressBook(1);
        $address->save();
    }

    public function toBraintreeAddress($address)
    {
        if ($address)
        {
            return array(
                'firstName'         => $address->getFirstname(),
                'lastName'          => $address->getLastname(),
                'company'           => $address->getCompany(),
                'streetAddress'     => $address->getStreet(1),
                'extendedAddress'   => $address->getStreet(2),
                'locality'          => $address->getCity(),
                'region'            => $address->getRegion(),
                'postalCode'        => $address->getPostcode(),
                'countryCodeAlpha2' => $address->getCountry(), // alpha2 is the default in magento
            );
        }
        else
        {
            return array();
        }
    }

    public function toMageAddress($address)
    {
        if ($address)
        {
            return array(
                'street'     => array(
                    $address->streetAddress,
                    $address->extendedAddress),
                'firstname'  => $address->firstName,
                'lastname'   => $address->lastName,
                'city'       => $address->locality,
                'region'     => $address->region,
                'postcode'   => $address->postalCode,
                'country_id' => $address->countryAlpha2
            );
        }
        else
        {
            return array();
        }
    }

    public function useVault()
    {
        return $this->_useVault;
    }
}
