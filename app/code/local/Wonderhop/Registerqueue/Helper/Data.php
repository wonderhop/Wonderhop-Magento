<?php class Wonderhop_Registerqueue_Helper_Data extends Mage_Core_Helper_Abstract {

	function isActive() {
		if ((int)Mage::getStoreConfig('Wonderhop_Registerqueue/general/registerqueue_active') == 1) return true;
		return false;
	}

	function getActivationPeriod() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/general/registerqueue_queue');
	}

	function getNeededInvitations() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/general/registerqueue_invitations_needed');
	}

	function getTemplateId_InQueue() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/email_template/in_queue');
	}

	function getTemplateId_Invited() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/email_template/person_invited_email');
	}

	function getTemplateId_QueueFinished() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/email_template/queue_finished');
	}

	function getTemplateId_QueueFinishedByInvitations() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/email_template/queue_finished_by_invitations');
	}

	function addEmailToQueue($email) {
		if($email == '' || $email == NULL) return false;
		//check if email already queued
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_email', array('like' => $email));
		if(count($model)) {
			return false; //email already queued
		}
		try {
			$registerqueue_referral_code = Mage::getSingleton('generic/data')->newReferralCode();
			$registerqueue_referral_id   = Mage::getModel('core/cookie')->get('wonderhop_r');
			$model = Mage::getModel('registerqueue/registerqueue');
			$data = array(
				'registerqueue_email' => $email,
				'registerqueue_date' => date('Y-m-d H:i:s'),
				'registerqueue_referral_code' => $registerqueue_referral_code,
				'registerqueue_referral_id' => $registerqueue_referral_id
			);
			$model->setData($data)->save();
			$this->checkReferrer($registerqueue_referral_id);

			//send email, explaining that he is placed in queue
			$template_id = $this->getTemplateId_InQueue();
			Mage::helper('mails')->sendTransactionalEmail($template_id, $email, null, null, array('invite_friend_url' => 'r='.$registerqueue_referral_code, 'email' => rawurlencode($email)));

			//set cookie with referral_code, allowing customer to access invite-friends page
			Mage::getModel('core/cookie')->set('referral_code', $registerqueue_referral_code, 604800); //7 days
			Mage::getModel('core/cookie')->set('referral_email', $email, 604800); //7 days
			return true;
		} catch (Exception $e) {
			return false; //something unexpected happened
		}
	}

	// check if needed number of friends joined, and finish queue
	function checkReferrer($referrer_id) {
		if($referrer_id != '' && !is_null($referrer_id)) {
			$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_referral_id', $referrer_id);
			$invitations_needed = $this->getNeededInvitations();
			if(count($model) >= $invitations_needed) {
				$referrer_email = '';
				// check referrer email in register queue table
				$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_referral_code', $referrer_id);
				if(count($model)) {
					$model = $model->getFirstItem();
					$referrer_email = $model->getData('registerqueue_email');
				} else {
					//referrer email not found, meaning that referrer is not in queue
					return false;
				}
				//finish queue
				return $this->completeQueue($referrer_email, true, true);
			}
		}
		return false;
	}

	function getReferralCode($customer_email) {
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection();
		$model->addFilter('registerqueue_email', array('like' => $customer_email));
		$model->load();
		if(count($model)) {
			$model = $model->getFirstItem();
			$referral_code = $model->getData('registerqueue_referral_code');
			if($referral_code != '' && !is_null($referral_code)) return $referral_code;
		}
		return Mage::getSingleton('generic/data')->newReferralCode();
	}

	function getReferrerId($customer_email) {
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection();
		$model->addFilter('registerqueue_email', array('like' => $customer_email));
		$model->load();
		if(count($model)) {
			$model = $model->getFirstItem();
			$referrer_id = $model->getData('registerqueue_referral_id');
			if($referrer_id != '' && !is_null($referrer_id)) return $referrer_id;
		}
		if (isset($_COOKIE['wonderhop_r'])) return $_COOKIE['wonderhop_r'];
		return NULL;
	}

	function canCompleteRegistration($email) {
		// already activated
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_email', array('like' => $email))->addFilter('registerqueue_invited', '1');
		if(count($model)) {
			return true;
		}
		// activate if not already activated, and queue period is over
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection();
		$model->addFilter('registerqueue_invited', 0);
		$model->addFilter('registerqueue_email', array('like' => $email));
		$model->getSelect()->where('DATE_ADD(registerqueue_date, INTERVAL ' . $this->getActivationPeriod() . ' HOUR) <= "' . date('Y-m-d H:i:s') . '"');
		if(count($model)) {
			return $this->completeQueue($email, false);
		}
		return false;
	}

	function completeQueue($email, $send_email = true, $queue_finished_by_invitations = false) {
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_email', array('like' => $email));
		if(count($model)) {
			try {
				$model = $model->getFirstItem();
				$model->setRegisterqueueInvited(1);
				$model->setRegisterqueueInvitedDate(date('Y-m-d H:i:s'));
				$model->save();

				// send email
				if($send_email) {
					$template_id = $this->getTemplateId_QueueFinished();
					if($queue_finished_by_invitations) {
						$template_id = $this->getTemplateId_QueueFinishedByInvitations();
					}
					Mage::helper('mails')->sendTransactionalEmail($template_id, $email, null, null, array('email' => rawurlencode($email)));
				}
				return true;
			} catch (Exception $e) {
				return false; //something unexpected happened
			}
		}
		return false;
	}

	// return a list of emails that are going to be activate now.
	function getQueuedEmails() {
		$return = array();
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection();
		$model->addFilter('registerqueue_invited', 0);
		$model->getSelect()->where('DATE_ADD(registerqueue_date, INTERVAL ' . $this->getActivationPeriod() . ' HOUR) <= "' . date('Y-m-d H:i:s') . '"');
		if(count($model)) foreach ($model as $email) $return[$email->getData('registerqueue_email')] = $email->getData('registerqueue_email');
		return $return;
	}
}
