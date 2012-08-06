<?php class Wonderhop_Sales_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() {
		$this->loadLayout(array('default'));
		 
	    if( $this->getRequest()->isPost() && $this->getRequest()->getParam('email')) {
	        $email = $this->getRequest()->getParam('email');
		    $block = $this->getLayout()->getBlock('wonderhop.login');
            $block->setData('email', $email);
            if ($this->getRequest()->getParam('url')) {
                $block->setData('url', $this->getRequest()->getParam('url'));
            }
        }
		$this->renderLayout();
    }

    
    
    public function mailcheckerAction()
    {

        if( $this->getRequest()->isPost() ) {
            $post  = $this->getRequest()->getPost();
            if( $post) {
                $mail_to_check = $this->getRequest()->getParam('email') ;

                    $customers = Mage::getModel('customer/customer')->getCollection();
                $customers->addAttributeToFilter( 'email', $mail_to_check );
                $customers->load();

                if ($customers->count()) {
                            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('ERR')));
                        } else {
                            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('OK')));
                    }
                } else {
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('ERR')));
                }
        } else {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('ERR')));
        }
    }
    
    
    /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {
            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(
                    Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                )) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        // Rebuild referer URL to handle the case when SID was changed
                        $referer = Mage::getModel('core/url')
                            ->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }
    
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
     /**
     * Login post action
     */
    public function loginPostAction()
    {
        
        $redirect_url = '/sales';
        if($this->getRequest()->getPost('url')) {
            $url = $this->getRequest()->getPost('url');
             
            $oRewrite = Mage::getModel('core/url_rewrite')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->loadByRequestPath($url);
            if ($oRewrite->getProductId() || $oRewrite->getCategoryId()) {
                $redirect_url = '/' . $url;
            }
        }
        if ($this->_getSession()->isLoggedIn()) {
           
                 
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array($redirect_url)));
            return;
        }
        $session = $this->_getSession();
        
        if ($this->getRequest()->isPost()) {
           
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                     
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array($redirect_url)));
                    
                    return;
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

 
}
