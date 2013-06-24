<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Shipping extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
	  $fieldset = $form->addFieldset('form_shipping', array('legend'=>Mage::helper('supplier')->__('Shipping Settings')));	  
	  $id = $this->getRequest()->getParam('id');
      if (isset($id)) {
          $nameDisabled = true;
		  $passwd = false;
      } else {
          $nameDisabled = false;
		  $passwd = true;
      }
	
	    $fieldset->addField('shipping_cost', 'text', array(
          'label'     => Mage::helper('supplier')->__('Default shipping cost'),
          'name'      => 'shipping_cost',
		  'class'     => 'validate-number',
        ));
		
		$fieldset->addField('shipping_cost_free', 'text', array(
		  'label'     => Mage::helper('supplier')->__('Free Shipping Above'),
		  'name'      => 'shipping_cost_free',
		  'class'     => 'validate-number',
		));  

      if ( Mage::getSingleton('adminhtml/session')->getWebData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWebData());
          Mage::getSingleton('adminhtml/session')->setWebData(null);
      } elseif ( Mage::registry('web_data') ) {
          $form->setValues(Mage::registry('web_data')->getData());
      }
      return parent::_prepareForm();
  }
}