<?php
class Magentomasters_Supplier_Block_Adminhtml_Templates_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("supplier_form", array("legend"=>Mage::helper("supplier")->__("Item information")));

				
						$fieldset->addField("title", "text", array(
						"label" => Mage::helper("supplier")->__("Title"),
						"name" => "title",
						));
					
						//$fieldset->addField("header", "textarea", array(
						//"label" => Mage::helper("supplier")->__("Header"),
						//"name" => "header",
						//'style'   => "height: 100px; width: 600px;",
						//));
					
						$fieldset->addField("body", "textarea", array(
						"label" => Mage::helper("supplier")->__("Body"),
						"name" => "body",
						'style'   => "height: 600px; width: 600px;",
						));
					
						$fieldset->addField("item", "textarea", array(
						"label" => Mage::helper("supplier")->__("Item"),
						"name" => "item",
						'style'   => "height: 300px; width: 600px;",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getTemplatesData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getTemplatesData());
					Mage::getSingleton("adminhtml/session")->setTemplatesData(null);
				} 
				elseif(Mage::registry("templates_data")) {
				    $form->setValues(Mage::registry("templates_data")->getData());
				}
				return parent::_prepareForm();
		}
}
