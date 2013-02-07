<?php class Wonderhop_Productshare_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		if(Mage::helper('customer')->isLoggedIn() && $this->getRequest()->isPost() && (int)Mage::helper('customer')->getCustomer()->getData('can_use_share_coupon') != 1) {
			$post = $this->getRequest()->getPost();
			$post_id = $this->getRequest()->getParam('post_id');
			if($post && $post_id) {
				Mage::helper('customer')->getCustomer()->setCanUseShareCoupon(1)->save();
				// send email
				$template_id = Mage::getStoreConfig('productshare/general/mail_template');
				$mail = Mage::helper('customer')->getCustomer()->getEmail();
				Mage::helper('mails')->sendTransactionalEmail($template_id, $mail, null, null, array());
				$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('OK')));
				return;
			}
		}
		$this->_redirect('/');
	}

}
