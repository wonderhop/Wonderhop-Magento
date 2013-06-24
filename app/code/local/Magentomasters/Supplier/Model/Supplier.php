<?php

class Magentomasters_Supplier_Model_Supplier extends Mage_Core_Model_Abstract {
    private $_userTbl;

    public function _construct() {
        parent::_construct();
        $this->_init('supplier/supplier');

        $this->_userTbl = Mage::getSingleton('core/resource')->getTableName('supplier_users');
        
    }

    public function getSupplier($productId)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "
            SELECT *
            FROM ".$this->_metaTbl." AS supplierMeta
            INNER JOIN ".$this->_userTbl."
                ON supplierMeta.supplier_id = ".$this->_userTbl.".id
            WHERE supplierMeta.product_id = '{$productId}'
        ";
        $result = $connect->query( $query );

        return $result->fetch();
    }

    public function getSuppliers()
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "
            SELECT users.id AS id, users.name AS name, users.surname AS surname
            FROM ".$this->_userTbl." AS users
        ";
        $result = $connect->query( $query );
        return $result->fetchAll();
    }


    public function getSupplierSettings($storeId)
    {
        $settings = Mage::getStoreConfig('supplier',$storeId);
		
        foreach($settings as $_settings) {
            foreach ($_settings as $key=>$val){
                $resultWithKeys[$key] = $val;
            }
        }
        return $resultWithKeys;
    }

    public function getSupplierIdByAttribute($productId) {
        
		$attributeid = $this->getSupplierAttributeId();
		$optionid = $this->getSavedOptionValue($attributeid,$productId);
		$supplierName = $this->getOptionValue($optionid);
		
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "
            SELECT user.id
            FROM ".$this->_userTbl." AS user
            WHERE user.name='{$supplierName}'
        ";
        $result = $connect->query( $query );
        $supplierId = $result->fetch();
        return $supplierId['id'];
    }

    public function getSupplierByAttribute($productId) {
		
		$attributeid = $this->getSupplierAttributeId();
		$optionid = $this->getSavedOptionValue($attributeid,$productId);
		$supplierName = $this->getOptionValue($optionid);
		
		Mage::log('Suppliername:' . $supplierName, null, "Ultimate_Dropship.log");
		
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "
            SELECT user.*
            FROM ".$this->_userTbl." AS user
            WHERE user.name='{$supplierName}'
        ";
        $result = $connect->query( $query );
        return $result->fetch();
    }

    public function getSupplierById($supplierId){
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "
            SELECT user.id, user.name
            FROM ".$this->_userTbl." AS user
            WHERE user.id='{$supplierId}'
        ";
        $result = $connect->query( $query );
        return $result->fetch();

    }
	
	 public function getSupplierByName($name){
       $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
       $table = Mage::getSingleton('core/resource')->getTableName('supplier_users');
	   $query = "SELECT * FROM ".$this->_userTbl." WHERE name='" . $name . "'";
       $result = $connect->query( $query );
       return $result->fetch();

    }

    public function getOrderByProduct($product_list) {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        if (is_array($product_list)) {
           $in_list = implode(',',$product_list);
        } else {
            $in_list = $product_list;
        }
        if (!isset($in_list)) {
            $in_list = '0';
        }
        $query = "SELECT o.increment_id as order_id
            FROM " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item'). " oi
            INNER JOIN " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order'). " o ON o.entity_id = oi.order_id
            WHERE product_id IN ({$in_list}) ORDER BY o.increment_id DESC";

        $result = $connect->query( $query );
        return $result->fetchAll();
    }

    public function getShippingDateByOrderId($order_list){
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        if (is_array($order_list)) {
            foreach($order_list as $v) {
                $order_array[] = $v['order_id'];
            }
            $in_list = implode(',',$order_array);
        } else {
            $in_list = $order_list;
        }
        $query = "SELECT order_increment_id, created_at AS shipping_date, shipping_name 
                FROM " . Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_grid'). "
                WHERE order_increment_id IN ({$in_list})";
        $result = $connect->query( $query );
        $orders = $result->fetchAll();
        foreach ($orders as $_order) {
            $order_info[$_order['order_increment_id']]['shipping_date'] = $_order['shipping_date'];
            $order_info[$_order['order_increment_id']]['shipping_name'] = $_order['shipping_name'];
        }
        return $order_info;
    }

    public function getOrderCommentsByOrderId ($order_id) {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "SELECT * FROM " . Mage::getSingleton('core/resource')->getTableName('sales_flat_order_status_history'). "
                    WHERE parent_id = '{$order_id}'";
        $result = $connect->query( $query );
        return $result->fetchAll();
    }
	
	public function getEmailTemplates(){
		
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
        $result = $connect->query("SELECT * FROM " . Mage::getSingleton('core/resource')->getTableName('supplier_templates'));
        $categories = $result->fetchAll();
        $newArray = array();
			$newArray['0'] = "Default";
        foreach($categories as $category){
            $newArray[$category['id']] = $category['title'];
        }
	 	return $newArray;
	}
	
	public function getSupplierAttributeId(){
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$attribute_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
		$query = "SELECT attribute_id FROM " . $attribute_table . " WHERE attribute_code='supplier'";	
		$result = $connect->query($query);
		$result = $result->fetch();
		return $result['attribute_id'];
	}
	
	public function getSupplierOptionsId($suppliername){
	
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
					
		$attribute_id = $this->getSupplierAttributeId();	
		$option_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option');
		$option_value_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
			
		$query = "SELECT p.attribute_id, v.value, MAX(p.option_id) AS option_id FROM " . $option_table . " AS p INNER JOIN
	" . $option_value_table . " AS v  ON p.option_id = v.option_id  AND v.store_id  = 0
	WHERE p.attribute_id=". $attribute_id ." AND v.value='" . $suppliername . "' ";	
	
		 $result = $connect->query($query);
		 
		 return $result->fetch();
	
	}
	
	public function getAttributeLabel($attribute_code){
		
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$attribute_table = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
		$query = "SELECT frontend_label FROM " . $attribute_table . " WHERE attribute_id='".$attribute_code."'";	
		$result = $connect->query($query);
		$result = $result->fetch();
		return $result['frontend_label'];
	}
	
	public function getOptionValue($option_id){
		
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
		$query = "SELECT value FROM " . $table . " WHERE option_id='".$option_id."'";	
		$result = $connect->query($query);
		$result = $result->fetch();
		return $result['value'];
	}
	
	public function deleteOptionValue($option_id){
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
		$table = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option');
		$query1 = "DELETE FROM " . $table . " WHERE option_id='".$option_id."'";
		$query2 = "DELETE FROM " . $table . " WHERE option_id='".$option_id."'";	
		$connect->query($query1);
		$connect->query($query2);
	}
	
	public function getSavedOptionValue($attribute_id,$product_id){
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_int');
		$query = "SELECT value FROM " . $table . " WHERE attribute_id='".$attribute_id."' AND entity_id='" . $product_id . "'";	
		$result = $connect->query($query);
		$result = $result->fetch();
		return $result['value'];
	}
}