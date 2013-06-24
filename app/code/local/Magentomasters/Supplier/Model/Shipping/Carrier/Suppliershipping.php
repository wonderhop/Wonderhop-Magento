<?php

class Magentomasters_Supplier_Model_Shipping_Carrier_Suppliershipping extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{

	protected $_code = 'suppliershipping';
	protected $_request = null;
	protected $_result = null;

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{

		if (!$this->getConfigFlag('active')) {
			return false;
		}

		$this->setRequest($request);

		$value = Mage::getModel('shipping/rate_result_method');
		$value->setCarrier('suppliershipping');
		$value->setMethodTitle('Shipping Costs');
		$value->setMethod('suppliershipping');

		$result = Mage::getModel('shipping/rate_result');

		$cart = Mage::getSingleton('checkout/cart');
		$totals = $cart->getQuote()->getTotals();
		$country = $cart->getQuote()->getShippingAddress()->getCountry();
		$region = $cart->getQuote()->getShippingAddress()->getRegion();
		$nosupplier = false;
		
		Mage::log($country,null, "Shipping_Country.log");
		Mage::log($region,null, "Shipping_Region.log");

		foreach($request->getAllItems() as $item ){
			
			$itemData = $item->getData();
			$qty = $item->getQty();
			$supplierModel = Mage::getModel('supplier/supplier');
			$attribute_id = $supplierModel->getSupplierAttributeId();
			$product_id = $item->getProductId();
			$suppliercheck = $supplierModel->getSavedOptionValue($attribute_id,$product_id); 								
			if($suppliercheck) {
				$suppliername = $supplierModel->getOptionValue($suppliercheck);	
				$supplierRes = $supplierModel->getSupplierByName($suppliername);
				$supplierId = $supplierRes['id'];
				$suppliers[$supplierId]['supplier'] = $supplierRes;
				$suppliers[$supplierId]['products'][$item->getProductId()] = $item;
				//Mage::log($itemData['sku'] . ' supplier', null, "Ultimate_Dropship.log");
			} else {
				$nosupplier = true;
				//$nosuppliertotal += ($item->getProduct()->getFinalPrice() * $qty);
				$nosuppliertotal += $item->getRowTotal();
				$nosupplierweight += $item->getRowWeight();
				//Mage::log($itemData['sku'] . ' nosupplier', null, "Ultimate_Dropship.log");
			}

		}
		
		$cost_calculation = $this->getConfigData('cost_calculation');
		
		
		//Mage::log($suppliers, null, "Ultimate_Dropship.log");
		
		$total = "";
				
		foreach ($suppliers as $key => $supplier){	
		
			$supplierId = $key;			
			$cost = $supplier['supplier']['shipping_cost'];
	
			if($cost_calculation==1) {
				$total += $cost; 
			} 
			elseif($cost_calculation==2) {
				if($cost > $highest){
					$highest = $cost; 
				}
				$total = $highest;
			} elseif ($cost_calculation==3){
				
				$freeshipping = $supplier['supplier']['shipping_cost_free']; 
				$supplierproducts =  $supplier['products'];
				$suppliertotal = '';
				$totalweight = '';
				
				foreach($supplierproducts as $item){
					//$itemtotal = $item->getProduct()->getFinalPrice() * $qty;
					//$itemtotal = $item->getProduct()->getRowTotal();
					$suppliertotal += $item->getRowTotal();
					$totalweight += $item->getRowWeight();
				}
				
				if($suppliertotal > $freeshipping){
					$cost = 0;
				}
				
				$total += $cost; 
				
				Mage::log('Total Supplier ' . $totalweight , null, "Shipping_Total.log");
				Mage::log('Total Supplier ' . $suppliertotal , null, "Shipping_Total.log");
				
			} elseif ($cost_calculation==4){
				
				
			}
			
		}
		
		if($nosupplier){
			$base_minimum = Mage::getStoreConfig('carriers/suppliershipping/default_order_amount_free');
			$base_costs = Mage::getStoreConfig('carriers/suppliershipping/default_shipping_rate_no_supplier');
			
			//Mage::log('Total No supplier ' . $nosuppliertotal, null, "Ultimate_Dropship.log");
			
			if($nosuppliertotal < $base_minimum){
				$total = $total + $base_costs;
			}
		}
		
		$value->setPrice($total);

		$result->append($value);

		$this->_result = $result;

		return $this->getResult();
	}

	public function setRequest(Mage_Shipping_Model_Rate_Request $request)
	{
		$this->_request = $request;

		$r = new Varien_Object();

		$this->_rawRequest = $r;

		return $this;
	}

	public function getResult()
	{
	   return $this->_result;
	}

	public function getCode($type, $code='')
	{
		$codes = array(
			'method'=>array(
				'FREIGHT'    => Mage::helper('usa')->__('Freight')
			)
		);

		if (!isset($codes[$type])) {
			//throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid ODFL CGI code type: %s', $type));
			return false;
		} elseif (''===$code) {
			return $codes[$type];
		}

		if (!isset($codes[$type][$code])) {
			// throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid ODFL CGI code for type %s: %s', $type, $code));
			return false;
		} else {
			return $codes[$type][$code];
		}
	}

	/**
	 * Get allowed shipping methods
	 *
	 * @return array
	 */
	public function getAllowedMethods()
	{
		$allowed = explode(',', $this->getConfigData('allowed_methods'));
		$arr = array();
		foreach ($allowed as $k) {
			$arr[$k] = $this->getCode('method', $k);
		}
		return $arr;
	}

	public function proccessAdditionalValidation( Mage_Shipping_Model_Rate_Request $request )
	{

		if(!count($request->getAllItems())) {
			return $this;
		}

		$errorMsg = '';
		$configErrorMsg = $this->getConfigData('specificerrmsg');
		$defaultErrorMsg = Mage::helper('shipping')->__('The shipping module is not available.');
		$showMethod = $this->getConfigData('showmethod');

		if (!$errorMsg && !$request->getDestPostcode() && $this->isZipCodeRequired()) {
			$errorMsg = Mage::helper('shipping')->__('This shipping method is not available, please specify ZIP-code');
		}

		if ($errorMsg && $showMethod) {
			$error = Mage::getModel('shipping/rate_result_error');
			$error->setCarrier($this->_code);
			$error->setCarrierTitle($this->getConfigData('title'));
			$error->setErrorMessage($errorMsg);
			return $error;
		} elseif ($errorMsg) {
			return false;
		}
		return $this;

		return $this;

	}

	public function isStateProvinceRequired()
	{
		return false;
	}

	public function isCityRequired()
	{
		return false;
	}

	public function isZipCodeRequired()
	{
		return false;
	}

}

?>