<?php class Wonderhop_Sales_ShopsController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() { 
		$this->loadLayout(array('default'));
		 
		$this->renderLayout();
    }

    
    
}
