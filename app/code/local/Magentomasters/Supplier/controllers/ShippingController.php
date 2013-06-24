<?php
class Magentomasters_Supplier_ShippingController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
    }
	
	public function gridAction() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
    }
	
	public function viewAction() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
    }
	
	public function shipAction() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
    }
	
	public function addshipmentAction(){
			
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        
        if( $supplierId && $supplierId != "logout") {
	        	
	        $post = $this->getRequest()->getPost(); 
	        
	        if ($post) {
	            	
	            $orderId = $post['order_id'];
	        	$itemsQty = $post['ship_qty'];
				$tracking = $post['tracking'];
	        	
	        	try {
	        		$this->completeAndShip($orderId,$itemsQty,$tracking);
	        	} catch (Exception $e) {
					Mage::getSingleton('core/session')->addError($e->getMessage());
					$this->_redirectUrl( Mage::getUrl() . 'supplier/order');
	        	}
				
	        }      
	        
	        Mage::getSingleton('core/session')->addSuccess("Succesfully Shipped");
	        $this->_redirectUrl(Mage::getUrl().'supplier/order');
		
		} else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }	
    }
	
	public function completeAndShip($orderId,$itemsQty,$tracking){
        $email = true; // <-- Must be users email address  $order->getCustomerEmail()
        $carrier = 'custom';
        $includeComment = false;
        $comment = "The order is shipped by the supplier";
        $order = Mage::getModel('sales/order')->load($orderId);
        $convertor = Mage::getModel('sales/convert_order');
        $shipment = $convertor->toShipment($order);

        foreach ($order->getAllItems() as $k=>$orderItem) {
            	
			if (!$orderItem->getQtyToShip()) {
        		continue;
        	}
			if ($orderItem->getIsVirtual()) {
           		continue;
        	}
			 	
			$item = $convertor->itemToShipmentItem($orderItem);	
			
			$productId = $orderItem->getProductId();
		
			if($itemsQty[$productId]) {
                $item->setQty($itemsQty[$productId]);
                $shipment->addItem($item);
            }			
        }
        
        $carrierTitle = NULL;

        if ($carrier == 'custom') {
            $carrierTitle = 'Playtimes';
        }
        foreach ($tracking as $data) {
            $track = Mage::getModel('sales/order_shipment_track')->addData($data);
            $shipment->addTrack($track);
        }

        $shipment->register();
        $shipment->addComment($comment, $email && $includeComment);
        $shipment->setEmailSent(true);
        $shipment->getOrder()->setIsInProcess(true);

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder())
            ->save();

        $shipment->sendEmail($email, ($includeComment ? $comment : ''));
		$order->setStatus('Complete');
		$order->addStatusToHistory($order->getStatus(), 'Order Completed because every item have been shipped', false);
        $shipment->save();
	
    }

	public function addTrackAction()
    {
 		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
		    $post = $this->getRequest()->getPost();
	        
	        if ($post) {	
				
				$orderId = $this->getRequest()->getPost('order_id');
				$shippingId = $this->getRequest()->getPost('shipping_id');
		        $carrier = $this->getRequest()->getPost('carrier');
		        $number  = $this->getRequest()->getPost('number');
		        $title  = $this->getRequest()->getPost('title');
				
				if($shippingId && $carrier && $number && $title){
				
			        try {
			        	
			            $shipment = Mage::getModel('sales/order_shipment')->load($shippingId);
			            
			            if ($shipment) {
			                	
			                $track = Mage::getModel('sales/order_shipment_track')->setNumber($number)->setCarrierCode($carrier)->setTitle($title);
			                
			                $shipment->addTrack($track)->save();
							
							$this->_redirectUrl(Mage::getUrl().'supplier/shipping/grid/order_id/' . $orderId);
			
			            } else {
							//$this->_redirectUrl(Mage::getUrl().'supplier/order');
			            }
			        } catch (Mage_Core_Exception $e) {
			            
			        } 
		        }
			}
		} else {
			$redirectPath = Mage::getUrl() . "supplier/";
			$this->_redirectUrl( $redirectPath );
        }	
    }

	public function emailAction(){
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
			$shipmentId = $this->getRequest()->getParam('shipment_id');		
			if($shipmentId){
	        	try{
				   $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId); 
				} catch (Exception $e) {
			       $this->_getSession()->addError($this->__('Cannot send shipment information.'));
			    }
	        	try {
		            if ($shipment) {
		                $shipment->sendEmail(true)
		                    ->setEmailSent(true)
		                    ->save();
		                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
		                   ->getUnnotifiedForInstance($shipment, Mage_Sales_Model_Order_Shipment::HISTORY_ENTITY_NAME);
		                if ($historyItem) {
		                    $historyItem->setIsCustomerNotified(1);
		                    $historyItem->save();
		                }
		                Mage::getSingleton('core/session')->addSuccess($this->__('The shipment has been sent.'));
		            }
		       	} catch (Mage_Core_Exception $e) {
		            Mage::getSingleton('core/session')->addError($e->getMessage());
		        } catch (Exception $e) {
		            Mage::getSingleton('core/session')->addError($this->__('Cannot send shipment information.'));
		        }
	        } else{
				Mage::getSingleton('core/session')->addError($this->__('Cannot send shipment information.'));
	        }
			$this->_redirect('*/*/view', array(
		            'shipping_id' => $this->getRequest()->getParam('shipment_id')
		    ));
		} else {
			$redirectPath = Mage::getUrl() . "supplier/";
			$this->_redirectUrl( $redirectPath );
        }		
	}
	
 
}