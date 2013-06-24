<?php class Magentomasters_Supplier_Model_Schedule extends Mage_Core_Model_Abstract {

	public function dropship(){
		$suppliers = Mage::getModel('supplier/supplier')->getCollection();		
		foreach ($suppliers as $supplier){
			$supplier = $supplier->getData();
			if($supplier['schedule_enabled']==2){
				$schedule = json_decode($supplier['schedule']);
				$lastrun = $supplier['schedule_dropship_date'];
				$run  = $this->checkschedule($schedule,$lastrun);
				if($run && $schedule){
					try {
						
						Mage::log('Run Cron Dropship', null, 'cron7.log');
						
						$dropshiporders = Mage::getModel('supplier/dropshipitems')->getCollection();
						$dropshiporders->addFieldToSelect('order_id');
						$dropshiporders->addFieldToFilter('status', array('eq' => 2));
						$dropshiporders->addFieldToFilter('supplier_id', array('eq' => $supplier['id']));
						$dropshiporders->getSelect()->group('order_id');
		 				$dropshiporders->getSelect()->distinct(true);							
						//Mage::log($dropshipitems->getSelect(), null, 'cron.log');

						foreach ($dropshiporders as $dropshiporder){
							
							$dropshiporder = $dropshiporder->getData();
							
							$dropshipitems = Mage::getModel('supplier/dropshipitems')->getCollection();
							$dropshipitems->addFieldToSelect('id'); 
							//$dropshipitems->addFieldToSelect('order_id');
							$dropshipitems->addFieldToSelect('order_item_id');
							$dropshipitems->addFieldToFilter('status', array('eq' => 2));
							$dropshipitems->addFieldToFilter('order_id', array('eq' => $dropshiporder['order_id']));
							$dropshipitems->addFieldToFilter('supplier_id', array('eq' => $supplier['id']));
							
							Mage::log('Order '. $dropshiporder['order_id'], null, 'cron_orders.log');
							
							$supplierList = array();
							$items = array();
							
							foreach ($dropshipitems as $dropshipitem){
								$dropshipitem = $dropshipitem->getData();
								$order_item_id = $dropshipitem['order_item_id'];
								Mage::log('Items '. $dropshipitem['order_item_id'], null, 'cron_orders.log');
								$item = Mage::getModel('sales/order_item')->load($order_item_id);
								$item['dropshipid'] = $dropshipitem['id'];
								$items[] = $item;
							}

							$supplier['cartItems'] = $items;
							$supplierList[$supplier['id']] = $supplier;
						
							Mage::log($supplierList, null, 'supplierList.log');
						
							Mage::getModel('supplier/observer')->cron($dropshiporder['order_id'],$supplierList);
						}

						$current_date_time = date("Y-m-d H:m:s");
						$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
						$table = Mage::getSingleton('core/resource')->getTableName('supplier_users');
						$query = "UPDATE ".$table." SET schedule_dropship_date='".$current_date_time."' WHERE id=".$supplier['id'];
						$connect->query($query);
											
					} catch (exception $e){
						Mage::log($e, null, 'cron-exception.log');
					}
				}
			}	
		}
						
	}

	private function checkschedule($schedule,$lastrun){
		
		$current_day = date('l');
		$current_hour = date('H') . ':00';
		$current_date_time = date("Y-m-d H:m:s");
		$lastrun = strtotime($lastrun);
		$lastrun_hour = date('H',$lastrun) . ':00';
		$lastrun_day = date('l',$lastrun);
		
		Mage::log($current_hour, null, 'cron.log');  
		
		if($current_hour!=$lastrun_hour){	
			foreach ($schedule->days as $day) {
				if($day==$current_day){
					Mage::log('We have a job on ' .$day, null, 'cron.log');
					foreach($schedule->hours as $hour){
						//Mage::log($hour,null,'cron.log');			
						if($hour==$current_hour){
							Mage::log('We have a job this hour ' .$hour, null, 'cron.log'); 
							return true;
						} else {
							Mage::log('Nothing this hour ' .$hour, null, 'cron.log');
						}
					
					}
				} 
			}
		} else {
			Mage::log("I allready been fired this hour!", null, 'cron.log');
		}
	}
	
	public function importStockScheduled(){
			
		$suppliers = Mage::getModel('supplier/supplier')->getCollection();
			
		foreach ($suppliers as $supplier){
			
			if($supplier['schedule_import_stock_enabled']==2){	 
				$schedule = json_decode($supplier['schedule_import_stock']);
				$lastrun = $supplier['schedule_import_stock_date'];
				
				$run = $this->checkschedule($schedule,$lastrun);

				if($run){
					$this->importStock('cron',$supplier['id']);
					$current_date_time = date("Y-m-d H:m:s");
					try{
            			//$supplier->setScheduleImportStockDate($current_date_time)->save();
						
						$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
						$table = Mage::getSingleton('core/resource')->getTableName('supplier_users');
						$query = "UPDATE ".$table." SET schedule_import_stock_date='".$current_date_time."' WHERE id=".$supplier['id'];
						$connect->query($query);
						
						Mage::log("Runned", null, 'Supplier-Import-Cron.log');
					} catch(Exception $e){
						Mage::log($e, null, 'Supplier-Import-Exception.log');
					}
				}
			}
		}
	}
	
		
	public function importStock($source,$supplierid){

		$supplier = Mage::getModel('supplier/supplier')->load($supplierid)->getData();
		// $source =="cron" or $source == "manual" 
		//$url = "http://www.heesbeen.nu/csv/test2.xml"; 
		Mage::log($supplier, null, 'Supplier-Import-Supplier.log');
		
		$url = $supplier['schedule_import_stock_url']; 
		$divider = $supplier['schedule_import_stock_divider']; 
		$type = $supplier['schedule_import_stock_type']; 
		$skufield = $supplier['schedule_import_stock_sku']; 
		$qtyfield = $supplier['schedule_import_stock_qty']; 
		
		if($type==1 && $url && $divider){
			$headers = array();
			$mapping = array();	
			$file = fopen($url, "r");
			if($file){
				while (($result = fgetcsv($file)) !== false)
				{
	    			$csv[] = $result;
				}
				fclose($file);
			}
			$headers = $csv['0'];			
			foreach ($headers as $key => $value) {
				$mapping[$value] = $key;
				$checkmapping[$value] = $key + 1;
			}
  			unset($csv['0']); // Remove headers
			if(!empty($checkmapping[$qtyfield]) && !empty($checkmapping[$skufield])){
				foreach ($csv as $item) {
					$qty = $item[$mapping[$qtyfield]];
					$qty = str_replace(',', '.', $qty);
					$sku = $item[$mapping[$skufield]];
					Mage::log($item, null, 'Supplier-Import-Item.log');
					$savestock = $this->savestock($sku, $qty);
					if($source=="manual"){
						Mage::getSingleton('adminhtml/session')->addSuccess($savestock);
					} else {
						Mage::log($savestock, null, 'Supplier-Import-Log.log');
					}	
				}
			} else {
				$msgmapping = implode(', ', $headers);
				$msg = 'On of the Collumns or the file is not Found. These collumns are found ' . $msgmapping;
				if($source=="manual"){
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('supplier')->__($msg));
				} else {
					Mage::log($msg, null, 'Supplier-Import-Log.log');
				}
			}
		} elseif($type==2){
			$xml = simplexml_load_file($url);
			if($xml){
				$skus = $xml->xpath("/KMSProducts/Product/Itemcode");
				$qtys = $xml->xpath("/KMSProducts/Product/Stock");
				$i = 0;
				if($skus && $qtys){
					foreach($qtys as $qty){
						$qty = (string) $qty[0];
						$items[]['qty'] = $qty; 
					}	
					foreach($skus as $sku){
						$sku = (string) $sku[0];
						$items[$i]['sku'] = $sku;
						$i++;
					}
					foreach($items as $item){
						$qty = $item['qty'];
						$qty = str_replace(',', '.', $qty);
						$sku = $item['sku'];					
						$savestock = $this->savestock($sku, $qty);
						if($source=="manual"){
							Mage::getSingleton('adminhtml/session')->addSuccess($savestock);
						} else {
							Mage::log($savestock, null, 'Supplier-Import-Log.log');
						}
					}
				} else {
					$msg = 'On of the Collumns or the file is not Found.';
					if($source=="manual"){
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('supplier')->__($msg));
					} else {
						Mage::log($msg, null, 'Supplier-Import-Log.log');
					}
				}
			}	
		}
				 
	} 

	public function savestock($sku,$qty){	
		$productId = Mage::getModel('catalog/product')->getIdBySku($sku);
		if($productId){
			if(is_numeric($qty)){
				if(!$qty || $qty==0){
					$stockData = array('qty' => $qty,'is_in_stock' => 0);	
				} else {
					$stockData = array('qty' => $qty,'is_in_stock' => 1);		
				}		 	
			 	$product = Mage::getModel('catalog/product' )->load($productId);
			 	$product->setStockData($stockData);
			 	$product->save();
			 	//$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
				//$table = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');
				//$query = "UPDATE $table SET qty=".$stockData['qty'].", is_in_stock=".$stockData['is_in_stock']." WHERE product_id=$productId";
				//$connect->query($query);			
				$msg = 'Product stock updated: ' . $sku .' qty: ' . $qty;
			} else {
				$msg = 'Qty was not a number: ' . $sku .' qty: ' . $qty;;
			}
		} else {
			$msg = 'Product not found: ' . $sku .' qty: ' . $qty;
		}
		return $msg;		
	}

}

?>