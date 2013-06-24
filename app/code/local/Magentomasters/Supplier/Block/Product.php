<?php class Magentomasters_Supplier_Block_Product extends Mage_Core_Block_Template { 

	public function getCollection(){
		
		$session = Mage::getSingleton('core/session');
		$supplierId = $session->getData('supplierId');
		$supplier =  Mage::getModel('supplier/supplier')->getSupplierById($supplierId);
		
		$suppliername = $supplier['name'];
		$attribute = Mage::getModel('supplier/supplier')->getSupplierOptionsId($suppliername);
		
		$collection = Mage::getModel('catalog/product')->getCollection();
		$collection->addFieldToFilter('supplier', $attribute['option_id']);
		//$collection->addAttributeToFilter('type_id', array ('simple','downloadable'));
		$collection->addAttributeToFilter('type_id',array('in' => array ('simple','downloadable')));
		$collection->addAttributeToSelect('*');
		
		return $collection;
		
	}


}