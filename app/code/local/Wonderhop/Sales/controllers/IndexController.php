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

    public function templateAction() {
        $templateId = Mage::getStoreConfig('Wonderhop_Sales/general/daily_newsletter_template',Mage::app()->getStore()); 
        $template = Mage::getModel('core/email_template');
        $template->load($templateId);
        $url =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $templateProcessed = $template->getProcessedTemplate(array('url' => $url), true);
        echo $templateProcessed;
    }
    
    public function savegmsgAction() {
        $session = Mage::getSingleton('customer/session');
        //if ( ! $session->isLoggedIn()) return;
        $customer = $session->isLoggedIn() ? $session->getCustomer() : NULL;
        $customer_name = $customer ? preg_replace( '/\s\-/',' ',$customer->getFirstname().' '.$customer->getLastname()) : 'Guest';
        if ( ! $this->_isAjaxPost()) { error_log('NOT is_ajax_post'); return; }
        foreach(array('to','from','message','is') as $_var) {
            $field = "gift_{$_var}_text";
            if ($_var == 'is') $field = 'gift_is_gift';
            $$field = '';
            if ($session->getData($field)) $$field = $session->getData($field);
            if (isset($_POST[$field])) {
                $session->setData($field, $_POST[$field]);
                $$field = $_POST[$field];
            }
            if ($_var == 'from' and ! trim($$field)) {
                $$field = $customer_name;
                $session->setData($field, $$field);
            }
            if ($_var == 'is' and $$field === '') {
                $$field = (int)$$field;
                $session->setData($field, $$field);
            }
        }
    }
    
    public function preparebuygiftcardAction()
    {
        $session = Mage::getSingleton('customer/session');
        $is_nlg = (isset($_COOKIE['curio_nlg']) and ($_COOKIE['curio_nlg'] == '1'));
        $product = (isset($_GET['product']) and is_numeric($_GET['product'])) ? Mage::getModel('catalog/product')->load($_GET['product']) : null;
        $ammount = (isset($_GET['giftcard_ammount']) and is_numeric($_GET['giftcard_ammount'])) ? abs(intval($_GET['giftcard_ammount'])) : 0;
        $email = (isset($_GET['gift_to_email']) and strpos($_GET['gift_to_email'], '@')) ? $_GET['gift_to_email'] : '';
        if (( ! $session->isLoggedIn() and ! $is_nlg) or ! $product or !$product->getId() or ! $ammount or ! $email) {
            header('Location: '.Mage::getBaseUrl());
            exit;
        }
        $cartHelper = Mage::helper('checkout/cart');
        $cart = $cartHelper->getCart();
        $checkout = Mage::getSingleton('checkout/session');
        $checkout->clear();
        $cart->addProduct($product , array('product'=> $product->getId(), 'qty'=>$ammount));
        $cart->save();
        $checkout->setIsGiftcardCheckout(true);
        $session->setIsGiftcardCheckout(true);
        $checkout->setRecipientEmail($email);
        //Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        
        header('Location: '.Mage::getBaseUrl().'onestepcheckout');
        exit;
    }
    
    
    protected function _isAjaxPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) 
            and strtolower($_SERVER['REQUEST_METHOD']) == 'post'
            and isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
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
        Mage::getSingleton('core/session')->setCustomerLoggedIn(1);
        $redirect_url = '/shops';
        if($this->getRequest()->getPost('url')) {
            $url = preg_replace('/\/?\?.*/', '',  $this->getRequest()->getPost('url'));
             
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
