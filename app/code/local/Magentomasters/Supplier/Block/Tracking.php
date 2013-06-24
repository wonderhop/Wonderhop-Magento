<?php
class Magentomasters_Supplier_Block_Tracking extends Mage_Core_Block_Template {


    public function getCarriers()
    {
        $carriers = array();
        $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers();
        $carriers['custom'] = Mage::helper('sales')->__('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }
	
	public function getOrder($orderid){
		
		 if(!$orderid){ $orderid = $this->getRequest()->getParam('order_id'); }
		 
		 $order = Mage::getModel('sales/order')->load($orderid);
		 
		 return $order;
	}
	
	public function getTrackings($orderid){
	
		$order = $this->getOrder($orderid);
		
		$trackings = $order->getTrackingNumbers();
		
		$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($order)->load();
		
		foreach ($shipmentCollection as $shipment){
            // This will give me the shipment IncrementId, but not the actual tracking information.
            foreach($shipment->getAllTracks() as $tracknum)
            {
                $tracknums[]= $tracknum->getCarrier();
            }

        }
		
		return $tracknums;	
		
	}
	
}