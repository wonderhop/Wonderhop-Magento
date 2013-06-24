<?php class Magentomasters_Supplier_Model_Order extends Mage_Core_Model_Abstract {

	public function getShipments($order)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE order_id=".$order;
		$result = $connect->query( $query );
        return $result->fetchAll();
    }
	
	public function getOrderIdByShippingId($shippingid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE entity_id=".$shippingid;
		$result = $connect->query( $query );
       return $result->fetch();
	}
	
	public function getShipment($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE entity_id=".$parentid;
        $result = $connect->query( $query );
        return $result->fetch();
    }
	
	public function getShipmentItems($parentid,$productIds)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_item');
		$query = "SELECT * FROM $table WHERE parent_id=$parentid AND product_id IN ($productIds)";
		$result = $connect->query($query);
        return $result->fetchAll();
    }
		
	public function getTrackings($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track');
		$query = "SELECT * FROM ".$table." WHERE parent_id=".$parentid;
		$result = $connect->query( $query );
        return $result->fetchAll();
    }

	public function checkOrderAuth($supplierId,$orderId){
		$items = $this->getCartItemsBySupplier($supplierId,$orderId);
		foreach($items as $item){
			if($item->getProductId()){
				return true;
			} else {
				return false;
			}
		}
	}

	public function getCartItemsBySupplier($supplierId,$orderId){
		$order = Mage::getModel('sales/order')->load($orderId);	
		$items = $order->getAllItems();
		$status = array(1,5);	
		
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection();
		$collection->addFieldToSelect('product_id');
		$collection->addFieldToFilter('supplier_id',$supplierId);
		$collection->addFieldToFilter('order_id',$orderId);
		$collection->addFieldToFilter('status', array('in' => $status));
		
		$product_list = $collection->getData();
		$products = array();
			
		foreach($product_list as $product){
			$products[] = $product['product_id'];
		}
		
		foreach ($items as $itemid => $item) {				
			if(!in_array($item->getProductId(),$products)){
				unset($items[$itemid]);
			} 
		}  
		return $items;
	}

}