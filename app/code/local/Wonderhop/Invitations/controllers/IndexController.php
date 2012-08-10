<?php class Wonderhop_Invitations_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() {
        if(!Mage::getSingleton('customer/session' )->isLoggedIn()) die ('access denied');
		$this->loadLayout(array('default'));
		$this->renderLayout();
    }
    
    public function sendAction() {
        if(!Mage::getSingleton('customer/session' )->isLoggedIn()) die ('access denied');
        $post = $this->getRequest()->getPost();
        
        if ( $post ) {
            $session      = Mage::getSingleton('core/session');
            $mail_string  = $post['emails'];
            $mails        = explode(',', $mail_string);
            
            $template_id   = Mage::getStoreConfig('Wonderhop_Sales/general/invite_friends_email_template', Mage::app()->getStore());	
		    $customer      = Mage::getSingleton('customer/session' )->getCustomer();
		    $customer_name = $customer->getFirstname() . " " . str_replace("-", '', $customer->getLastname());
		  
            $extra_vars  = array('customer_name' => $customer_name, 'url' => Mage::getUrl('?r=' . $customer->getReferralCode()));
            
            try {
                foreach($mails as $mail) {
                    $mail = trim($mail);
                    if (!$mail) {
                        continue;
                    }
                    $customers = Mage::getModel('customer/customer')->getCollection();
                    $customers->addAttributeToFilter( 'email', $mail );
                    $customers->load();
                    
                    #if customer already here do not send email
                    if (!$customers->count()) {
                        Mage::helper('mails')->sendTransactionalEmail($template_id, $mail, null, null, $extra_vars);
                    }
                    
                    $data = array('customer_fk'            => $customer->getId(), 
                                  'template_id'            => $template_id, 
                                  'invitation_send_date'   => gmdate("Y-m-d H:i:s"),
                                  'sent_to'                => $mail
                    );
                    
              	    $model = Mage::getModel('invitations/invitations')->setData($data);
              	    $model->save();
                }    
                        
                $session->addSuccess(Mage::helper('invitations')->__('The emails were sent. 
                <script type="text/javascript">
                    mixpanel.track("Invitations sent page");
                 </script>'));
                $this->_redirectReferer();

                return;
            } catch (Exception $e) {
                 
                $session->addError(Mage::helper('invitations')->__('There was some error processing your request.'));
				 
                $this->_redirectReferer();
                return;
            }
            
        }
    }
    
    
}
