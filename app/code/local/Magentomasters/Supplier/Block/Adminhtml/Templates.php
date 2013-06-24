<?php


class Magentomasters_Supplier_Block_Adminhtml_Templates extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_templates";
	$this->_blockGroup = "supplier";
	$this->_headerText = Mage::helper("supplier")->__("Templates Manager");
	$this->_addButtonLabel = Mage::helper("supplier")->__("Add New Item");
	parent::__construct();
	
	}

}