<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Schedule extends Mage_Adminhtml_Block_Widget_Form
{
				
  protected function _prepareForm()
  {
	  $form = new Varien_Data_Form();
      $this->setForm($form);	  	
      $fieldset = $form->addFieldset('form_schedule', array('legend'=>Mage::helper('supplier')->__('Schedule Settings')));
	  
      $this->setTemplate('supplier/schedule.phtml');
	    
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
}
	