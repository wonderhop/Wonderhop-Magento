<?php class Wonderhop_Registerqueue_Helper_Data extends Mage_Core_Helper_Abstract {

	function isActive() {
		if ((int)Mage::getStoreConfig('Wonderhop_Registerqueue/general/registerqueue_active') == 1) return true;
		return false;
	}

	function getActivationPeriod() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/general/registerqueue_queue');
	}

	function getTemplateId() {
		return (int)Mage::getStoreConfig('Wonderhop_Registerqueue/general/registerqueue_email_template');
	}

	function addEmailToQueue($email) {
		if($email == '' || $email == NULL) return false;
		//check if email already queued
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_email', $email);
		if(count($model)) {
			return false; //email already queued
		}
		try {
			$model = Mage::getModel('registerqueue/registerqueue');
			$data = array(
				'registerqueue_email' => $email,
				'registerqueue_date' => date('Y-m-d H:i:s')
			);
			$model->setData($data)->save();
			return true;
		} catch (Exception $e) {
			return false; //something unexpected happened
		}
	}

	function canCompleteRegistration($email) {
		// already activated
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_email', $email)->addFilter('registerqueue_invited', '1');
		if(count($model)) {
			return true;
		}
		// activate if not already activated, and queue period is over
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection();
		$model->addFilter('registerqueue_invited', 0);
		$model->addFilter('registerqueue_email', $email);
		$model->getSelect()->where('DATE_ADD(registerqueue_date, INTERVAL ' . $this->getActivationPeriod() . ' HOUR) <= "' . date('Y-m-d H:i:s') . '"');
		if(count($model)) {
			return $this->completeQueue($email, false);
		}
		return false;
	}

	function completeQueue($email, $send_email = true) {
		$model = Mage::getModel('registerqueue/registerqueue')->getCollection()->addFilter('registerqueue_email', $email);
		if(count($model)) {
			try {
				$model = $model->getFirstItem();
				$model->setRegisterqueueInvited(1);
				$model->setRegisterqueueInvitedDate(date('Y-m-d H:i:s'));
				$model->save(false);

				// send email
				if($send_email) {
					Mage::helper('mails')->sendTransactionalEmail($this->getTemplateId(), $email, null, null, array('email' => rawurlencode($email)));
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
