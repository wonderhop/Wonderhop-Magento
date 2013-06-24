<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('web_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('supplier')->__('Supplier Settings'));
  }

  protected function _beforeToHtml()
  {
	  $id = $this->getRequest()->getParam('id');
    if(isset($id)){
      $active = false;
    } else {
      $active = true;
    }

	  $this->addTab('form_dashboard', array(
          'label'     => Mage::helper('supplier')->__('Dashboard'),
          'title'     => Mage::helper('supplier')->__('Dashboard'),
          'content'   => $this->getLayout()->createBlock('supplier/adminhtml_web_edit_tab_dashboard')->toHtml(),
      ));
	  
	  $this->addTab('form_dropshipments', array(
          'label'     => Mage::helper('supplier')->__('Dropshipments'),
          'title'     => Mage::helper('supplier')->__('Dropshipments'),
      	  'url'       => $this->getUrl('*/*/dropshipments', array('_current' => true)),
          'class'     => 'ajax',	
	  ));	
      
	  $this->addTab('form_section', array(
          'label'     => Mage::helper('supplier')->__('Info'),
          'title'     => Mage::helper('supplier')->__('Info'),
          'content'   => $this->getLayout()->createBlock('supplier/adminhtml_web_edit_tab_supplier')->toHtml(),
          'active'    => $active 
    ));
	  
	  $this->addTab('form_email', array(
          'label'     => Mage::helper('supplier')->__('Email'),
          'title'     => Mage::helper('supplier')->__('Email'),
          'content'   => $this->getLayout()->createBlock('supplier/adminhtml_web_edit_tab_email')->toHtml(),
      ));
	  
	  $this->addTab('form_xml', array(
          'label'     => Mage::helper('supplier')->__('Xml/Csv'),
          'title'     => Mage::helper('supplier')->__('Xml/Csv'),
          'content'   => $this->getLayout()->createBlock('supplier/adminhtml_web_edit_tab_xml')->toHtml(),
      ));
	  
	  $this->addTab('form_shipping', array(
          'label'     => Mage::helper('supplier')->__('Shipping'),
          'title'     => Mage::helper('supplier')->__('Shipping'),
          'content'   => $this->getLayout()->createBlock('supplier/adminhtml_web_edit_tab_shipping')->toHtml(),
      ));
	  
	  $this->addTab('form_import', array(
          'label'     => Mage::helper('supplier')->__('Import'),
          'title'     => Mage::helper('supplier')->__('Import'),
          'content'   => $this->getLayout()->createBlock('supplier/adminhtml_web_edit_tab_import')->toHtml(),
      ));
	  
	  $this->addTab('form_schedule', array(
          'label'     => Mage::helper('supplier')->__('Schedule Tasks'),
          'title'     => Mage::helper('supplier')->__('Schedule Tasks'),
          'content'   => $this->getLayout()->createBlock('supplier/adminhtml_web_edit_tab_schedule')->toHtml(),
      ));
	 
      return parent::_beforeToHtml();
  }
}