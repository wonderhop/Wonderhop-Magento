<?php 
class Wonderhop_Registerqueue_Model_Cron {
    
    public $logger; 
    public $debug;
    
    public function __construct() {
        $this->logger = Mage::getModel('core/log_adapter', 'registerqueue.log');
    } 
 
    public function getLogger() {
        return $this->logger;
    }
    
	function executeCron () {
		$helper = Mage::helper('wonderhop_registerqueue');
		if(!$helper->isActive()) return;
		$emails = $helper->getQueuedEmails();
		foreach($emails as $email) {
			$helper->completeQueue($email);
		}
	} 
}
