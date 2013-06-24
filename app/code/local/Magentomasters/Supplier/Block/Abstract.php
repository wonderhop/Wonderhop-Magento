<?php
class Magentomasters_Supplier_Block_Abstract extends Mage_Core_Block_Template {
		
	public function getSupplierId(){
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
		return $supplierId;
	}

	public function getOrderId() {
		$orderId = $this->getRequest()->getParam('order_id');
		return $orderId;
	}
	
	public function getOrderIdByShippingId($shippingid) {
		if(!$shippingid){
			$shippingid = $this->getRequest()->getParam('shipping_id');	
		}
		$shipping = Mage::getModel('supplier/order')->getOrderIdByShippingId($shippingid);
		return $shipping['order_id'];	
	}
	
	public function getOrder($orderId){
		
		$orderIdRequest = $this->getRequest()->getParam('order_id');
		$shippingId = $this->getRequest()->getParam('shipping_id');
		
		if($shippingId){
			$orderId = $this->getOrderIdByShippingId($shippingId);
		} elseif(!$orderId) {
			$orderId = $this->getRequest()->getParam('order_id');
		}
		$order = Mage::getModel('sales/order')->load($orderId); 
		return $order;
	}
	
	public function getCartItems($order){
		$supplierId = $this->getSupplierId();
		$items = Mage::getModel('supplier/order')->getCartItemsBySupplier($supplierId,$order->getEntityId());
		$bundle = false;	
		foreach ($items as $key => $item) {
			if($item->getParentItemId()){
			   	
			   $parent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
		       
		       if($parent->getProductType()=='bundle'){
				   	if($bundle==$item->getParentItemId()){
				   		unset($items[$key]);
				   	} else{
				   		$items[] = $parent;
				   		$bundle = $item->getParentItemId();
				   	}
		       } elseif($parent->getProductType()=='configurable'){
		       		unset($items[$key]);
					$items[] = $parent;
		       }
			   
			   if ($parent->getQtyToShip() < 1) { unset($items[$key]); }
			
			}
		}
		return $items;
	}
	
	public function canShip($orderId){
		if($orderId){
			$order = $this->getOrder($orderId);
		} else {
			$order = $this->getOrder();
		}
		$items = $this->getCartItems($order);
		
		if($order->getIsVirtual()==1){ return false; }
		
		foreach ($items as $key => $item) {
			 if($item->getParentItemId()){
			 	 $parent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
				 if ($parent->getQtyToShip() > 0) { return true; }
			 } else {
			 	 if ($item->getQtyToShip() > 0) { return true; }	
			 }
		}
		
		return false; 
	}
	
	public function getOrderItemsToShip($orderId){
		if($orderId){
			$order = $this->getOrder($orderId);
		} else {
			$order = $this->getOrder(null);
		}
		$items = $this->getCartItems($order);
		foreach ($items as $key => $item) {
             if ($item->getQtyToShip() < 1) { unset($items[$key]); }
		}
		
		return $items; 
	}
	
	
	public function getCartProductIds(){
		$order = $this->getOrder(null);
		$items = $this->getCartItems($order);
		$itemIds = array();
		foreach ($items as $item) {
			$itemIds[] = $item->getProductId();
		}
		return $itemIds;	
	}
	
	public function getOrderData($orderId) {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $supplierModel = Mage::getModel('supplier/supplier');
        $supplierInfo = $supplierModel->getSupplierById($supplierId);
        $supplierName = $supplierInfo['name'];
        $attrIdArray = $this->getAttrIdsByValue($supplierName);

		if(!$orderId){
        $orderId = $this->getRequest()->getParam('order_id');
		}
        /**
         * Use
         * $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
         * if it is needed to load by increment id
         */
        $order = Mage::getModel('sales/order')->load($orderId);
        $order_info = array(
            'id'            => $orderId,
            'order_num'     => $order->getRealOrderId(),
            'create_date'   => $order->getCreatedAtFormated('short'),
        );
        $items = $order->getAllItems();
        $itemcount=count($items);
        foreach ($items as $itemId => $item) {
            $attributeValue = Mage::getModel('catalog/product')
                            ->load($item->getProductId())
                            ->getAttributeText('supplier');
            if ($attributeValue == $supplierName) {
                $productsOptions = $item->getProductOptions();
                $product_list[$item->getProductId()] = array(
                    'id'        => $item->getProductId(),
                    'name'      => $item->getName(),
                    'sku'       => $item->getSku(),
                    'unitPrice' => $item->getPrice(),
                    'qty'       => (array (
                                        'ordered'   => $item->getQtyOrdered(),
                                        'invoice'   => $item->getQtyToInvoice(),
                                        'ship'      => $item->getQtyToShip(),
                                        'refund'    => $item->getQtyToRefund(),
                                        'cancel'    => $item->getQtyToCancel(),
                                    )
                    ),
                    'options'   => $productsOptions['options']
                );
            }

        }

        $order_data = array(
            'order_info' => $order_info,
            'customer_info' => $this->getCustomerData($order),
            'shipping_address' => $this->getShippingAddress($order),
            'billing_address' => $this->getBillingAddress($order),
            'comments' => $supplierModel->getOrderCommentsByOrderId($orderId),
            'products' => $product_list
        );
        return $order_data;
    }
	
}