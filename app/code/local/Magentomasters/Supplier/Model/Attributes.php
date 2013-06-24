<?php class Magentomasters_Supplier_Model_Attributes{


	public function getAllAttributes($product) {
        $entityType = Mage::getModel('eav/config')->getEntityType('catalog_product');
        $entityTypeId = $entityType->getEntityTypeId();
        $attribute_mas = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter($entityTypeId)->getItems();
		$product_attribute = Mage::getModel('catalog/product')->load($product->getId());
		
        foreach ($attribute_mas as $attribute) {
            $data = $attribute->getData();
            $frontend_input = $data['frontend_input'];
            $frontend_label = $data['frontend_label'];
            $attribute_code = $data['attribute_code'];
            if ($attribute_code == 'sku' || $attribute_code == 'price')
                continue;
            if ($frontend_input == 'select') {
                $value = $product_attribute ->getAttributeText($attribute_code);
            } else {
                $value = $product_attribute->getResource()->getAttribute($attribute_code)->getFrontend()->getValue($product_attribute);
            }
            if ($value == '' || is_array($value) || $frontend_label == '')
                continue;
            $attributes["attribute_" . $attribute_code] = $value;	
        }
       
        return $attributes;
    }

	public function getAllCustomOptions($item) {
			$customOptions = $item->getProductOptions();
			$customOptionsMas = array();
			if(isset($customOptions['options'])){				
					$customOptionsWithSku = array();
					if (isset($customOptions['options'])) {
						foreach ($customOptions['options'] as $option) {
							$key = $option['label'];	
							$customOptionsMas[$key] = $option['value'];
							//$customOptionsWithSku[$key] = $option['sku'];
						}
					}
					//$this->customOptions = $customOptionsWithSku;
			}
			if(isset($customOptions['additional_options'])){					
					if (isset($customOptions['additional_options'])) {
						foreach ($customOptions['additional_options'] as $option) {
							$key = $option['label'];
							$customOptionsMas[$key] = $option['value'];
						}
					}	
			} 
			
			if(isset($customOptions['bundle_options'])){					
					if (isset($customOptions['bundle_options'])) {
						foreach ($customOptions['bundle_options'] as $option) { 
							$key = $option['label'];
							$optionValue = "";
							foreach($option['value'] as $val){
								$optionValue .= $val['title'] ." ". $val['qty'] . "x<br/>";
							}
							$customOptionsMas[$key] = $optionValue;
						}
					}	
			} 
			if(isset($customOptions['info_buyRequest']['super_attribute'])){
					$super_attributes = $customOptions['info_buyRequest']['super_attribute'];
					foreach ($super_attributes as $label => $option){
						$frontendlabel = Mage::getModel('supplier/supplier')->getAttributeLabel($label);
						$frontendvalue = Mage::getModel('supplier/supplier')->getOptionValue($option);
						$customOptionsMas[$frontendlabel] = $frontendvalue;
					}
			}	
			return $customOptionsMas;
	}

	public function getCustomOptions($item){
        $customOptionsMas = $this->getAllCustomOptions($item);
        $ret = "";
        if (!empty($customOptionsMas)) {
            foreach ($customOptionsMas as $k => $v) {
                if ($v) {
                    $ret .= $k . ": " . $v . "<br/>";
                }
            }
            $ret .= "";
        }
		
		return $ret;
	}
   
   public function getCustomOptionsXml($item){
   		$customOptionsMas = $this->getAllCustomOptions($item);
		$ret = "";
        if (!empty($customOptionsMas)) {
            $ret .= "<product_options>\n";
            foreach ($customOptionsMas as $k => $v) {
                $tagName = 'option';
                $ret .= "<" . $tagName . ">\n";
                $ret .= "<option_name>$k</option_name><option_value>$v</option_value>\n";
                $ret .= "</" . $tagName . ">\n";
            }
            $ret .= "</product_options>\n";
        }
    
    	return $ret;
    }

}
	