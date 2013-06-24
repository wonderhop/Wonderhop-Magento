<?php
class Magentomasters_Supplier_Block_Order extends Magentomasters_Supplier_Block_Abstract{

    public function getOrders() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
		$status = array(1,5);
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToSelect('order_id')->addFieldToFilter('supplier_id',$supplierId)->addFieldToFilter('status', array('in' => $status));
		$collection->getSelect()->group('order_id');
	 	$collection->getSelect()->distinct(true);
		$order_list = $collection->getData();
        $limit = $this->getCurrentLimit();
        $filter = $this->getRequest()->getParam('status');
        $p = $this->getCurrentPage();

        $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $order_list))
				->addAttributeToSort('entity_id', 'DESC')
                ->setPageSize($limit)
                ->setPage($p, $limit);

        if ($filter) {
            foreach ($orders as $key => $order) {
                if ($filter=="Shipped" && $this->canShip($order->getEntityId())) {
                    $orders->removeItemByKey($key);
                } elseif($filter=="Waiting" && !$this->canShip($order->getEntityId())) {
                	$orders->removeItemByKey($key);
                }
            }
            Mage::register("supplier_order_filter",$filter);
        }
        Mage::register("order_page_count", array("item_count"=>$orders->getSize(),"last_page"=>$orders->getLastPageNumber()));
        return $orders;
    }

    public function getPageUrl($page=1){
        $limit = $this->getCurrentLimit();
        $currentUrl = $this->getCurrentUrl();
        return $this->getBaseUrl() . "supplier/order/index" . '/limit/' . $limit . '/p/' . $page;
    }


    public function getCurrentUrl(){
        $urlRequest = Mage::app()->getFrontController()->getRequest();
        $urlPart = $urlRequest->getServer('ORIG_PATH_INFO');
        if(is_null($urlPart))
        {
            $urlPart = $urlRequest->getServer('PATH_INFO');
        }
        $urlPart = substr($urlPart, 1 );
        
        return $this->getUrl($urlPart);

    }


    public function getAvailableOrderLimit() {
        $mode = 'list';
        $perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
        $perPageValues = (string)Mage::getStoreConfig($perPageConfigKey);
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if (Mage::getStoreConfigFlag('catalog/frontend/list_allow_all')) {
            return ($perPageValues + array('all'=>$this->__('All')));
        } else {
            return $perPageValues;
        }
    }


    public function getCurrentLimit(){
        $limit = $this->getRequest()->getParam('limit');
        if (!isset ($limit)) { $limit = $this->getDefaultLimit();}
        return $limit;
    }


    public function getDefaultLimit(){
        $defaultLimit = Mage::getStoreConfig('catalog/frontend/list_per_page');
        return $defaultLimit;
    }

    
    public function getCurrentPage(){
        $defaultPage = 1;
        $page = $this->getRequest()->getParam('p');
        
        if (!isset ($page)) { $page = $defaultPage;}
        return $page;
    }

    public function getFilterValues(){
        return array(
            "" => "",
            "Shipped" => "Shipped",
            "Waiting" => "Waiting"
        );
    }

    public function getCurrentFilter(){
        return Mage::registry("supplier_order_filter");
    }
}