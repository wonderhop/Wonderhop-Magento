<?php class Magentomasters_Supplier_DropshipController extends Mage_Adminhtml_Controller_Action
{
	 protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }	
	
	 public function dropshipmassAction(){
		
		$action = $this->getRequest()->getParam('action');
		$orderIds = $this->getRequest()->getParam('order_ids');
		$i;
			
		foreach ($orderIds as $orderId)
                {				
					$order = Mage::getModel('sales/order')->load((int)$orderId);
					
					if($action=="dropship"){
						$i++;
						$observerModel = Mage::getModel('supplier/observer');
						$dd = $observerModel->manual($orderId);
					}
					
				}
				
				$this->_getSession()->addSuccess($this->__('Total of '.$i.' dropshipments were made.'));
				$this->_redirect('adminhtml/sales_order');			
    }

	public function dropshippingAction()
    {
       
	   return $this->getResponse()->setBody(
            	$this->getLayout()
                ->createBlock('supplier/adminhtml_tab_dropship')
                ->setIndex($this->getRequest()->getParam('index'))
                ->toHtml()
           );
    }
	
	public function dropshipmentAction(){
	
		$orderId = $this->getRequest()->getParam('order_id');
		$observerModel = Mage::getModel('supplier/observer');
		
		try {
               $dd = $observerModel->manual($orderId);
        }
		
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError("A error has occurred during dropshipment<br/><br/>" . $e);
			//Mage::getSingleton('adminhtml/session')->setFormData($data);
			$this->_redirect('adminhtml/sales_order/view/order_id/' . $orderId);	
			return;
		}
		
		$this->_getSession()->addSuccess($this->__('Order is dropshipped succesfully'));
		$this->_redirect('adminhtml/sales_order/view/order_id/' . $orderId);	
		
	}
	
}