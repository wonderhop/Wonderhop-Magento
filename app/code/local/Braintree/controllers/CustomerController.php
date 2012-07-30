<?php

require 'Mage/Adminhtml/controllers/CustomerController.php';

class Braintree_CustomerController extends Mage_Adminhtml_CustomerController
{

    public function deleteAction()
    {
        $braintree = Mage::getModel('braintree/paymentMethod');
        $customerId = $this->getRequest()->getParam('id');

        if ($customerId)
        {
            $braintree->deleteCustomer($customerId);
        }

        parent::deleteAction();
    }

    public function massDeleteAction()
    {
        $customerIds = $this->getRequest()->getParam('customer');

        if(is_array($customerIds))
        {
            $braintree = Mage::getModel('braintree/paymentMethod');
            foreach ($customerIds as $customerId)
            {
                $braintree->deleteCustomer($customerId);
            }
        }

        parent::massDeleteAction();
    }
}
