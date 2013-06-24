<?php
class Magentomasters_Supplier_Block_Header extends Magentomasters_Supplier_Block_Abstract{
	public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
	    $supplierId = $session->getData('supplierId');
	    if($supplierId && $supplierId != "logout" ) {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}	
}
	