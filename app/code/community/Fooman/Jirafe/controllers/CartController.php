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
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_CartController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn()) {
            $customerSession->setBeforeAuthUrl(Mage::getUrl('foomanjirafe/cart', array('_current'=>true)));
            $customerSession->addNotice(Mage::helper('foomanjirafe')->__('Please login to continue shopping.'));
            $this->_redirect('customer/account/login', array('_current'=>true));
        } else {
            $this->_redirect('checkout/cart', array('_current'=>true));
        }
    }
}
