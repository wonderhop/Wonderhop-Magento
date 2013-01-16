<?php class Wonderhop_Registerqueue_Model_Observer {

	public function bypassMagentoAccountCreate($observer) {
		if(Mage::helper('wonderhop_registerqueue')->isActive()) {
			Mage::app()->getFrontController()->getResponse()->setRedirect('/');
		}
	}
}
