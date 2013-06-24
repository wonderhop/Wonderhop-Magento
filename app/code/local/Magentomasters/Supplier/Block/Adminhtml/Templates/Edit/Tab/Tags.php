<?php
class Magentomasters_Supplier_Block_Adminhtml_Templates_Edit_Tab_Tags extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("supplier_form", array("legend"=>Mage::helper("supplier")->__("Item information")));

			 	$this->setTemplate('supplier/tags.phtml');
					

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
