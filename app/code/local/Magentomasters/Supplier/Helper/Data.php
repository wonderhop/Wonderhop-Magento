<?php
class Magentomasters_Supplier_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getSuppliers(){
		
		$options = array();
			
			$options[""] = "";
		
			$collection =  Mage::getModel('supplier/supplier')->getCollection();
			
			foreach($collection as $supplier)
			{
				if($supplier->getName()){	
				$options[$supplier->getName()] = $supplier->getName();
				}			
			}
		
		return $options;
		
	}
	
	public function getSupplierOptionsById(){
		$suppliers = Mage::getModel('eav/entity_attribute_option')->getCollection()->setStoreFilter()->join('attribute','attribute.attribute_id=main_table.attribute_id', 'attribute_code');
		foreach ($suppliers as $supplier){	
            if ($supplier->getAttributeCode() == 'supplier'){
            	$suppliers_options[$supplier->getOptionId()] = $supplier->getValue();
			}
        }
		return $suppliers_options;
	}
		
}