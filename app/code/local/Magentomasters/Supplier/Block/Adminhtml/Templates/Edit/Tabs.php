<?php
class Magentomasters_Supplier_Block_Adminhtml_Templates_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("templates_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("supplier")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("supplier")->__("Item Information"),
				"title" => Mage::helper("supplier")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("supplier/adminhtml_templates_edit_tab_form")->toHtml(),
				));
				
				$this->addTab("tags_section", array(
				"label" => Mage::helper("supplier")->__("Available Tags"),
				"title" => Mage::helper("supplier")->__("Available Tags"),
				"content" => $this->getLayout()->createBlock("supplier/adminhtml_templates_edit_tab_tags")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
