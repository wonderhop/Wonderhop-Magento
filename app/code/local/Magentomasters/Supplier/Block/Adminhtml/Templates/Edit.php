<?php
	
class Magentomasters_Supplier_Block_Adminhtml_Templates_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "supplier";
				$this->_controller = "adminhtml_templates";
				$this->_updateButton("save", "label", Mage::helper("supplier")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("supplier")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("supplier")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);
				
				$this->_addButton("copy", array(
					"label"     => Mage::helper("supplier")->__("Copy"),
					"onclick"   => "saveAndContinueEdit()",
					'onclick'   => 'location.href=\''. $this->getUrl('supplier/adminhtml_templates/copy',array('id'=>$this->getRequest()->getParam('id'))) .'\'',
					"class"     => "copy",
				), -100);

				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("templates_data") && Mage::registry("templates_data")->getId() ){

				    return Mage::helper("supplier")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("templates_data")->getId()));

				} 
				else{

				     return Mage::helper("supplier")->__("Add Item");

				}
		}
}