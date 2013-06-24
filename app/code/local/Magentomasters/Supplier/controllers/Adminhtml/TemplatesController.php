<?php

class Magentomasters_Supplier_Adminhtml_TemplatesController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("supplier/templates")->_addBreadcrumb(Mage::helper("adminhtml")->__("Templates  Manager"),Mage::helper("adminhtml")->__("Templates Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Supplier"));
			    $this->_title($this->__("Manager Templates"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Supplier"));
				$this->_title($this->__("Templates"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("supplier/templates")->load($id);
				if ($model->getId()) {
					Mage::register("templates_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("supplier/templates");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Templates Manager"), Mage::helper("adminhtml")->__("Templates Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Templates Description"), Mage::helper("adminhtml")->__("Templates Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("supplier/adminhtml_templates_edit"))->_addLeft($this->getLayout()->createBlock("supplier/adminhtml_templates_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("supplier")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Supplier"));
		$this->_title($this->__("Templates"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("supplier/templates")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("templates_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("supplier/templates");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Templates Manager"), Mage::helper("adminhtml")->__("Templates Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Templates Description"), Mage::helper("adminhtml")->__("Templates Description"));


		$this->_addContent($this->getLayout()->createBlock("supplier/adminhtml_templates_edit"))->_addLeft($this->getLayout()->createBlock("supplier/adminhtml_templates_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{
			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {
						$model = Mage::getModel("supplier/templates")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Templates was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setTemplatesData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setTemplatesData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}

		public function copyAction(){
			if($this->getRequest()->getParam("id")) {
				$id = $this->getRequest()->getParam("id");
				$template = Mage::getModel("supplier/templates")->load($id);
				$name = $template->getTitle();
				$template->setId(null);
				$template->setTitle('Copied ' . $name);		
			   	$template->save();
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Templates copied"));
				$this->_redirect("*/*/");
			}
		}

		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("supplier/templates");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
}
