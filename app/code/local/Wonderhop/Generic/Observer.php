<?php

class Wonderhop_Generic_Observer {
    
    
    public function on_sales_quote_save_before($observer)
    {
        // onestepcheckout does not do register_success , so we hook in here to see if it registers on checkout
        if ( ! Mage::registry('observing_customer_is_new')
          and ($observer->getQuote()->getCheckoutMethod() === 'register')
        ) {
            Mage::register('observing_customer_is_new', 1 , true);
        }
    }
    
    
    public function on_checkout_type_onepage_save_order_after($observer)
    {
        $customer = $observer->getQuote()->getCustomer();
        if (Mage::getSingleton('customer/session')->isLoggedIn()
          and Mage::registry('observing_customer_is_new')
        ) {
            Mage::getSingleton('generic/data')->oscRegisterCustomerData($customer, true);
            Mage::getSingleton('core/session')->setCustomerRegistered( 1 );
        }
    }
    
    public function on_checkout_onepage_controller_success_action($observer)
    {
        if ( ! Mage::registry('is_order_placed_success')) {
            Mage::register('is_order_placed_success', 1);
        }
    }
    
}
