<?php
require_once  Mage::getModuleDir('controllers', 'Mage_Checkout').DS.'CartController.php';
class Idev_OneStepCheckout_CartController extends Mage_Checkout_CartController
{
    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     */
    protected function _goBack()
    {

        if ($returnUrl = $this->getRequest()->getParam('return_url')) {
            // clear layout messages in case of external url redirect
            if ($this->_isUrlInternal($returnUrl)) {
                $this->_getSession()->getMessages(true);
            }
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            //if config enabled, clear messages and redirect to checkout
            if(Mage::getStoreConfig('onestepcheckout/direct_checkout/redirect_to_cart')){

                $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
                $allowedGroups = Mage::getStoreConfig('onestepcheckout/direct_checkout/group_ids');

                if(!empty($allowedGroups)){
                    $allowedGroups = explode(',',$allowedGroups);
                } else {
                    $allowedGroups = array();
                }

                if(!in_array($customerGroupId, $allowedGroups)){

                    $this->_getSession()->getMessages(true);
                    $this->_redirect('onestepcheckout', array('_secure'=>true));
                } else {
                    $this->_redirect('checkout/cart');
                }

            } else {
                $this->_redirect('checkout/cart');
            }


        }
        return $this;
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }
		if (Mage::helper('customer')->isLoggedIn() && $couponCode == 'ahDZw2' && (int)Mage::helper('customer')->getCustomer()->getData('can_use_share_coupon') != 1) {
			$this->_getSession()->addError(
                $this->__('You are not allowed to use "%s" coupon.', Mage::helper('core')->htmlEscape($couponCode))
            );
			$this->_goBack();
			return;
		}
        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if (strlen($couponCode)) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }

}
