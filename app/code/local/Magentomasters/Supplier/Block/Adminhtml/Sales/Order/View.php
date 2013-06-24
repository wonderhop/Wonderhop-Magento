<?php class Magentomasters_Supplier_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {

    public function  __construct() {

        parent::__construct();

		if (Mage::getStoreConfig('supplier/suppconfig/method') == 'manual' || Mage::getStoreConfig('supplier/suppconfig/method') == 'invoicemanual' ) {
						
				$order_id = $this->getRequest()->getParam('order_id');
				$check = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToFilter('order_id',$order_id)->count();
				$order = Mage::getModel('sales/order')->load($order_id);					
				
				if(!$check && $order->getState()!='canceled' && $order->getState()!='closed' || Mage::getStoreConfig('supplier/suppconfig/multiple')==1 && $order->getState()!='canceled' && $order->getState()!='closed'){
					$this->_addButton('Dropship', array(
					  'label'     => 'Dropship',
					  'url' => Mage::app()->getStore()->getUrl('supplier/dropship/dropship/'),		  
					  'onclick'   => 'setLocation(\'' . $this->getUrl('supplier/dropship/dropshipment') . '\')',
					));
				}
				
				
		}
	
	}
}