<?php class Wonderhop_Invitations_IndexController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction()
    {
        //if( ! Mage::getSingleton('customer/session' )->isLoggedIn()) die ('access denied');
        $this->loadLayout(array('default'));
        $this->renderLayout();
    }
    
	public function sendinvitesAction() {
		if(!Mage::getSingleton('customer/session' )->isLoggedIn()) {
			echo json_encode(array('status' => 'You must login to use this feature.'));
			return;
		}

		$post = $this->getRequest()->getPost();
		if(!$post) {
			echo json_encode(array('status' => 'failure'));
			return;
		}
        $nonce = Mage::getSingleton('customer/session')->getInviteNonce();
        if (!$nonce || $this->getRequest()->getParam('nonce') !== $nonce) {
            echo json_encode(array('status' => Mage::helper('invitations')->__('There was some error processing your request.')));
            return;
        }
		$session       = Mage::getSingleton('core/session');
		$mail_string   = $post['emails'];
		$mails         = explode(',', $mail_string);

		//this code parse email in case it come in format : Cosmin Ardeleanu <cosmin.ardeleanu@sinapticode.ro>
		//will return only the email part
        foreach ($mails as $key => $mail) {
			$final = $mail;
            if(strpos($mail,'<')) {
                $_mail = explode("<",$mail);
                foreach($_mail as $emi) {
                    if(strpos($emi,'@')) {
                        $final = str_replace(">","",$emi);
                    }
                }
            } else {
                $final = $emails;
            }
            $mails[$key] = $final;
        }

		$template_id   = Mage::getStoreConfig('Wonderhop_Sales/general/invite_friends_email_template', Mage::app()->getStore());    
		$customer      = Mage::getSingleton('customer/session' )->getCustomer();
		$customer_name = $customer->getFirstname() . " " . str_replace("-", '', $customer->getLastname());
		$extra_vars    = array('customer_name' => $customer_name, 'url' => Mage::getBaseUrl() . '?confirmation='.md5($customer->getId()).'&r=' . $customer->getReferralCode());
		try {
			foreach($mails as $mail) {
				$mail = trim($mail);
				if (!$mail) continue;
				$customers = Mage::getModel('customer/customer')->getCollection();
				$customers->addAttributeToFilter('email', $mail);
				$customers->load();
				#if customer already here do not send email
				if (!$customers->count()) {
					Mage::helper('mails')->sendTransactionalEmail($template_id, $mail, null, null, $extra_vars);
				}
				$data = array(
					'customer_fk'          => $customer->getId(), 
					'template_id'          => $template_id, 
					'invitation_send_date' => gmdate("Y-m-d H:i:s"),
					'sent_to'              => $mail
				);
				$model = Mage::getModel('invitations/invitations')->setData($data);
				$model->save();
			}    
			echo json_encode(array('status' => 'success'));
			return;
		} catch (Exception $e) {
			echo json_encode(array('status' => Mage::helper('invitations')->__('There was some error processing your request.')));
			return;
		}
    }
    
    public function sendAction() {
        if( ! Mage::getSingleton('customer/session' )->isLoggedIn()) die ('access denied');
        $post = $this->getRequest()->getPost();
        
        if ( $post ) {
            $session      = Mage::getSingleton('core/session');
            $mail_string  = $post['emails'];
            $mails        = explode(',', $mail_string);
            
            $template_id   = Mage::getStoreConfig('Wonderhop_Sales/general/invite_friends_email_template', Mage::app()->getStore());    
            $customer      = Mage::getSingleton('customer/session' )->getCustomer();
            $customer_name = $customer->getFirstname() . " " . str_replace("-", '', $customer->getLastname());
          
            $extra_vars  = array('customer_name' => $customer_name, 'url' => Mage::getUrl('?confirmation='.md5($customer->getId()).'&r=' . $customer->getReferralCode()));
            
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
                        
                $session->addSuccess(Mage::helper('wonderhop_invitations')->__('The emails were sent. 
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
