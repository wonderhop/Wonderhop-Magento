<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Dashboard extends Mage_Adminhtml_Block_Widget_Form
{
				
  protected function _prepareForm()
  {
	  $form = new Varien_Data_Form();
      $this->setForm($form);	  	
      $fieldset = $form->addFieldset('form_dashboard', array('legend'=>Mage::helper('supplier')->__('Supplier Dashboard')));
	  
      $this->setTemplate('supplier/dashboard.phtml');
	  
      if ( Mage::getSingleton('adminhtml/session')->getWebData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWebData());
          Mage::getSingleton('adminhtml/session')->setWebData(null);
      } elseif ( Mage::registry('web_data') ) {
          $form->setValues(Mage::registry('web_data')->getData());
      }
      return parent::_prepareForm();
  }

	public function getSupplier(){
		$supplier = Mage::getModel('supplier/supplier')->load($this->getRequest()->getParam('id'));
		return $supplier->getData();
	}
	
	public function getSales($type){
		$collection = $this->getCollection();
		$collection = $collection->getData();
		$total = 0; 
		foreach($collection as $item){
			$total += $item['price'];
		}
		if($type=='clean'){
			return $total;
		} else{		
			return Mage::helper('core')->currency($total,true,false);
		}
	}
	
	public function getCosts($type){
		$collection = $this->getCollection();
		$collection = $collection->getData();
		$total = 0;
		foreach($collection as $item){
			$total += $item['cost'];
		}
		if($type=='clean'){
			return $total;
		} else{		
			return Mage::helper('core')->currency($total,true,false);
		}
	}

	public function getProfit(){
		$sales = $this->getSales('clean');
		$costs = $this->getCosts('clean');
		$total =  $sales - $costs; 		
		return Mage::helper('core')->currency($total,true,false);
	}
	
	public function getPending(){
		$collection = $this->getCollection();
		$collection->addFieldToFilter('status', array('eq' => 1));
		$count = $collection->count();	
		return $count;
	}
	
	public function getCompleted(){
		$collection = $this->getCollection();
		$collection->addFieldToFilter('status', array('eq' => 5));
		$count = $collection->count();	
		return $count;
	}
	
	public function getScheduled(){
		$collection = $this->getCollection();
		$collection->addFieldToFilter('status', array('eq' => 2));
		$count = $collection->count();	
		return $count;
	}
	
	public function getCanceled(){
		$collection = $this->getCollection();
		$collection->addFieldToFilter('status', array('eq' => 3));
		$count = $collection->count();	
		return $count;
	}

	public function getRefunded(){
		$collection = $this->getCollection();
		$collection->addFieldToFilter('status', array('eq' => 4));
		$count = $collection->count();	
		return $count;
	}
	
	public function getCount(){
		$collection = $this->getCollection();
		$count = $collection->count();	
		return $count; 
	}

	private function getCollection(){
		$supplierID = $this->getRequest()->getParam('id');
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToFilter('supplier_id',$supplierID);
		return $collection; 
	}
}
	