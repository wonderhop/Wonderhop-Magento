<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Import extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
	  $fieldset = $form->addFieldset('form_import', array('legend'=>Mage::helper('supplier')->__('Import Settings')));	  
	  $id = $this->getRequest()->getParam('id');
      if (isset($id)) {
          $nameDisabled = true;
		  $passwd = false;
      } else {
          $nameDisabled = false;
		  $passwd = true;
      }
	
	    $fieldset->addField('schedule_import_stock_url', 'text', array(
          'label'     => Mage::helper('supplier')->__('Stock Import File Path'),
          'name'      => 'schedule_import_stock_url'
        ));
		
		if($id){
			$url = '<button style="" class="scalable " onclick="setLocation(\''. Mage::helper("adminhtml")->getUrl("supplier/adminhtml_web/importstock/",array("id"=> $id)) . '\')" type="button"><span>Update</span></button>';
			$fieldset->addField('', 'note', array(
	            'label'     => Mage::helper('supplier')->__('Stock Update Manual'),
	            'text'     => $url
      		));
		}
		
		$fieldset->addField('schedule_import_stock_type', 'select', array(
                'label'     => Mage::helper('supplier')->__('Stock Import File Type'),
                'name'      => 'schedule_import_stock_type',
                'values'    => array(
                        array(
                                'value'     => 0,
                                'label'     => Mage::helper('supplier')->__('XML'),
                        ),
                        array(
                                'value'     => 1,
                                'label'     => Mage::helper('supplier')->__('CSV'),
                        ),
                ),
      	));
		
		$fieldset->addField('schedule_import_stock_divider', 'text', array(
		  'label'     => Mage::helper('supplier')->__('Stock Import Divider'),
		  'name'      => 'schedule_import_stock_divider'
		));
		
		$fieldset->addField('schedule_import_stock_sku', 'text', array(
		  'label'     => Mage::helper('supplier')->__('Sku Collumn name'),
		  'name'      => 'schedule_import_stock_sku',
		  'note'	  => "Sku xml path OR Sku csv collumn name<br/>XML example: /Products/Product/Sku" 
		));
		
		$fieldset->addField('schedule_import_stock_qty', 'text', array(
		  'label'     => Mage::helper('supplier')->__('Qty Collumn name'),
		  'name'      => 'schedule_import_stock_qty',
		  'note'	  => "Qty xml path OR Qty csv collumn name<br/>XML example: /Products/Product/Qty" 
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