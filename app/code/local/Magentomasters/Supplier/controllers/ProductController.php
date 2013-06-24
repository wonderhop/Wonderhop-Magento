<?php class Magentomasters_Supplier_ProductController extends Mage_Core_Controller_Front_Action {
	
	public function preDispatch(){
		parent::preDispatch();	
		if(Mage::getStoreConfig('supplier/interfaceoptions/interface_enabled')=='0'){
			$redirectPath = Mage::getUrl();
			$this->_redirectUrl($redirectPath); 
		} else if(Mage::getStoreConfig('supplier/interfaceoptions/interface_stock')=='0'){
			$redirectPath = Mage::getUrl() . "supplier/order";
			$this->_redirectUrl($redirectPath); 
		}
	}
	public function indexAction() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        //$orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	
	public function saveAction(){
		
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        //$orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            
			
			 $data = $this->getRequest()->getPost();
			 
			 if($data['id']){
				 
				if(!$data['qty'] || $data['qty']==0){ 
			
					$stockData = array(
					  'qty' => $data['qty'],
					  'is_in_stock' => 0
					);
				
				} else {
					
					$stockData = array(
					  'qty' => $data['qty'],
					  'is_in_stock' => 1
					);
				}
					
			 	$productId = $data['id'];
			 	$product = Mage::getModel('catalog/product' )->load($productId);
			 	$product->setStockData($stockData);
			 	$product->save();
				
				echo "Product updated.";
				
			 }
			
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	
	}
	
}