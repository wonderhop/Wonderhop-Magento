<?php 
class Magentomasters_Supplier_Adminhtml_DropshipmentsController extends Mage_Adminhtml_Controller_Action {

    /**
     * Additional initialization
     *
     */

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/order')
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Shipments'),$this->__('Dropshipments'));
        return $this;
    }

    /**
     * Shipments grid
     */
    public function indexAction()
    {
       	
		$this->loadLayout();
		$this->_title($this->__('Sales'))->_title($this->__('Dropshipments'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Supplier Manager'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Supplier News'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('supplier/adminhtml_dropshipments'));
        $this->renderLayout();
    }
	
	public function exportCsvAction() {
		$fileName = 'dropshipments.csv';
		 
		$content = $this->getLayout ()->createBlock('supplier/adminhtml_dropshipments');
		
		$content->addColumnAfter('price', array(
				'header' => Mage::helper('supplier')->__('Price'),
				'index' => 'price',
		));
			
		$content->addColumnAfter('cost', array(
				'header' => Mage::helper('supplier')->__('Cost'),
				'index' => 'cost',
		));
		 
		$this->_prepareDownloadResponse($fileName, $content->getCsvFile());
 
	}

	public function emailAction(){
        $orderId = $this->getRequest()->getParam('id');
		$dropshipitem= Mage::getModel('supplier/dropshipitems')->load($orderId)->getData();
		$items = Mage::getModel('supplier/order')->getCartItemsBySupplier($dropshipitem[supplier_id],$dropshipitem[order_id]);
		$email = Mage::getModel('supplier/output')->getEmail($dropshipitem[order_id],$dropshipitem[supplier_id],$items);		
		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('supplier')->__('Email Sent'));
		$this->_redirect('*/*/');
	}
	
	public function printDropshipmentsAction(){
		$data = $this->getRequest()->getPost();
		$dropshipment_ids = $this->getRequest()->getPost('dropshipment_ids', array());
		$file = 'dropshipments_'.date("Ymd_His").'.pdf';
        $pdf = Mage::getModel('supplier/output')->getPdfs($dropshipment_ids);	    
		$this->_prepareDownloadResponse($file,$pdf,'application/pdf');
	}
	
	public function printPdfReportAction(){
		$data = $this->getRequest()->getPost();
		$dropshipment_ids = $this->getRequest()->getPost('dropshipment_ids', array());
		$file = 'report_'.date("Ymd_His").'.pdf';
        $pdf = Mage::getModel('supplier/output')->getPdfReport($dropshipment_ids);	    
		$this->_prepareDownloadResponse($file,$pdf,'application/pdf');
	}
	
}