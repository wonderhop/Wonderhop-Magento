<?php

class Wonderhop_Generic_Helper_Onestepcheckout_Extraproducts extends Idev_OneStepCheckout_Helper_Extraproducts {
    

    public function hasExtraProducts()
    {
        if ( ! parent::hasExtraProducts()) return false;
        foreach(Mage::helper('checkout/cart')->getCart()->getItems() as $item) {
            if ( ! Mage::getModel('catalog/product')->load($item->getProductId())->getFastShippingAvailable()) {
                return false;
            }
        }
        return true;
    }
}
