<?php
class Magentomasters_Supplier_Block_Adminhtml_Web extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_web';
    $this->_blockGroup = 'supplier';
    $this->_headerText = Mage::helper('supplier')->__('Supplier Manager');
    $this->_addButtonLabel = Mage::helper('supplier')->__('Add Supplier');
    parent::__construct();
  }
}