<?php
class Magentomasters_Supplier_Block_Shipping extends Magentomasters_Supplier_Block_Abstract{

	public function getShippingId() {
		$orderId = $this->getRequest()->getParam('shipping_id');
		return $orderId;
	}
	
	public function getShipments($orderid){
		$shipments = Mage::getModel('supplier/order')->getShipments($orderid);
		foreach ($shipments as $key => $shipment){
			$check = $this->checkShipments($shipment['entity_id']);
			if(!$check){
				unset($shipments[$key]);
			}
		}
		return $shipments;	
	}
	
	public function getShipment($parentid){
		$shipment = Mage::getModel('supplier/order')->getShipment($parentid);	
		return $shipment;
	}
	
	public function getShipmentitems($parentid){
		$productIds = $this->getCartProductIds();
		$productIds = implode(',',$productIds);	
		$shipmentitems = Mage::getModel('supplier/order')->getShipmentItems($parentid,$productIds);
		return $shipmentitems;
	}
	
	public function getTrackings($parentid){
		$trackings = Mage::getModel('supplier/order')->getTrackings($parentid);
		return $trackings;
	}
	
	public function checkShipments($parentid){
		$productIds = $this->getCartProductIds();
		$productIds = implode(',', $productIds);
		$check = Mage::getModel('supplier/order')->getShipmentItems($parentid,$productIds);
		if(empty($check)){
			return false;
		} else {
			return true;
		}
	}
}