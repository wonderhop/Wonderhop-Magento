<?php

require('Mage/Customer/controllers/AccountController.php');

class Braintree_CreditCardController extends Mage_Customer_AccountController
{
    public function indexAction()
    {
        $braintree = Mage::getModel('braintree/paymentmethod');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $braintree = Mage::getModel('braintree/paymentmethod');
        if($braintree->exists(Mage::getSingleton('customer/session')->getCustomer()->getId()))
        {
            $this->getLayout()->getBlock('braintree_creditcard_management')->setTemplate('braintree/creditcard/new_creditcard.phtml');
        }
        else
        {
            $this->getLayout()->getBlock('braintree_creditcard_management')->setTemplate('braintree/creditcard/new_customer.phtml');
        }
        $this->renderLayout();
    }


    public function createAction()
    {
        $braintree = Mage::getModel('braintree/paymentmethod');
        $result = $braintree->confirm();
        if ($result->success)
        {
            Mage::getSingleton('customer/session')->addSuccess('Credit card successfully created');
            $this->_redirect('braintree/creditcard/index');
        }
        else
        {
            Mage::register('braintree_result', $result);
            $this->newAction();
        }
    }

    public function deleteAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function deleteConfirmAction()
    {
        $braintree = Mage::getModel('braintree/paymentmethod');
        $result  = $braintree->deleteCard($_POST['token']);
        $this->_redirect('braintree/creditcard/index');
    }

    public function editAction()
    {
        if ($this->_hasToken())
        {
            $this->loadLayout();
            $this->renderLayout();
        }
        else
        {
            $this->_redirect('braintree/creditcard/index');
        }
    }

    public function updateAction()
    {
        $braintree = Mage::getModel('braintree/paymentmethod');
        $result = $braintree->confirm();
        if ($result->success)
        {
            Mage::getSingleton('customer/session')->addSuccess('Credit card successfully updated');
            $this->_redirect('braintree/creditcard/index');
        }
        else
        {
            Mage::register('braintree_result', $result);
            $this->editAction();
        }
    }

    private function _hasToken()
    {
        return Mage::app()->getRequest()->getParam('token') || Mage::registry('braintree_result');
    }
}
