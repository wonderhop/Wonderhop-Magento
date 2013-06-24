<?php

class Magentomasters_Supplier_Model_Output{
	
		const EMAIL = 'email';
    	const XML = 'xml';
		const ATTR = 'attr';	
		
		public function getPdf($order_id,$supplier_id,$items){
			$order = Mage::getModel('sales/order')->load($order_id);
			$supplier = Mage::getModel('supplier/supplier')->load($supplier_id)->getData();	
			$settings = $this->getSettings($order->getStoreId());
			$finalpdf = $this->processTemplate($order,$supplier,$items,'pdf');
			if(!$finalpdf){ $finalpdf = 'No Template Found'; }
			Mage::getModel('supplier/observer')->logging($finalpdf);
			require_once 'includes/tcpdf/tcpdf.php';
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->AddPage();						
			$pdf->writeHTML($finalpdf, true, false, true, false);
			$pdf->lastPage();
			return $pdf->Output(false, 'S');	 
		}
		
		public function getPdfs($dropshipment_ids){
			require_once 'includes/tcpdf/tcpdf.php';
			$dropshiporders = Mage::getModel('supplier/dropshipitems')->getCollection();
			$dropshiporders->addFieldToSelect('order_id');
			$dropshiporders->addFieldToSelect('supplier_id');
			$dropshiporders->addFieldToFilter('id', array('in' => $dropshipment_ids));
			$dropshiporders->getSelect()->group('order_id');
			$dropshiporders->getSelect()->distinct(true);							
			//Mage::log($dropshipitems->getSelect(), null, 'cron.log');
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
	
			foreach ($dropshiporders as $dropshiporder){
				$dropshipsuppliers = Mage::getModel('supplier/dropshipitems')->getCollection();
				$dropshipsuppliers->addFieldToSelect('order_id'); 
				$dropshipsuppliers->addFieldToSelect('supplier_id');
				$dropshipsuppliers->addFieldToFilter('order_id', array('eq' => $dropshiporder['order_id']));
				$dropshipsuppliers->getSelect()->group('supplier_id');
				$dropshipsuppliers->getSelect()->distinct(true);
				
				foreach ($dropshipsuppliers as $dropshipsupplier){
					$order = Mage::getModel('sales/order')->load($dropshiporder['order_id']);
					$supplier = Mage::getModel('supplier/supplier')->load($dropshipsupplier['supplier_id'])->getData();	
					$settings = $this->getSettings($order->getStoreId());
					$items = Mage::getModel('supplier/order')->getCartItemsBySupplier($dropshipsupplier['supplier_id'],$dropshiporder['order_id']);
					$finalpdf = $this->processTemplate($order,$supplier,$items,'pdf');
					$pdf->AddPage();						
					$pdf->writeHTML($finalpdf, true, false, true, false);
				}	
			}

			$pdf->lastPage();
			return $pdf->Output(false, 'S');	
		}

		public function getPdfReport($dropshipment_ids){
			require_once 'includes/tcpdf/tcpdf.php';
			
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			
			$dropshipsuppliers = Mage::getModel('supplier/dropshipitems')->getCollection(); 
			$dropshipsuppliers->addFieldToSelect('supplier_id');
			$dropshipsuppliers->addFieldToFilter('id', array('in' => $dropshipment_ids));
			$dropshipsuppliers->getSelect()->group('supplier_id');
			$dropshipsuppliers->getSelect()->distinct(true); 
			
			foreach ($dropshipsuppliers as $dropshipsupplier){
				
				$dropshipsupplier = $dropshipsupplier->getData();
				Mage::log($dropshipsupplier, null, 'pdfreport.log'); 
					
				$dropshipitems = Mage::getModel('supplier/dropshipitems')->getCollection();
				$dropshipitems->addFieldToSelect('order_item_id');
				$dropshipitems->addFieldToFilter('id', array('in' => $dropshipment_ids));
				$dropshipitems->addFieldToFilter('supplier_id', array('eq' => $dropshipsupplier['supplier_id']));
				$items = array();
				
				foreach ($dropshipitems as $dropshipitem){
					$dropshipitem = $dropshipitem->getData();
					
					Mage::log($dropshipitem, null, 'pdfreport.log');
					
					$items[] = Mage::getModel('sales/order_item')->load($dropshipitem['order_item_id']); 
				}
				
				$supplier = Mage::getModel('supplier/supplier')->load($dropshipsupplier['supplier_id'])->getData();
				$templates = $this->getTemplates($supplier,'base');		
				$finalpdf = $this->processitemTemplate($templates['pdfreportitems'],$items,'pdf',null,$supplier);
				
				foreach($supplier as $key => $value ){ $variables['supplier_' . $key] = $value; }	
					
				$variables['pdf_items'] = $finalpdf;

				//Mage::log($items, null, 'pdfreport.log'); 
				$processor = Mage::helper('cms')->getBlockTemplateProcessor();
				$processor->setVariables($variables);
				$finalpdf = $processor->filter($templates['pdfreport']); 	
				
				$pdf->AddPage();
				$pdf->writeHTML($finalpdf, true, false, true, false);			
			}
			
			$pdf->lastPage();
			return $pdf->Output(false, 'S');		
		}
		
		public function getEmail($order_id,$supplier_id,$items,$trigger){
			$order = Mage::getModel('sales/order')->load($order_id);
			$supplier = Mage::getModel('supplier/supplier')->load($supplier_id)->getData();
			$settings = $this->getSettings($order->getStoreId());

			$finalemail = $this->processTemplate($order,$supplier,$items,'email');

			$mail = new Zend_Mail();
            $mail->addTo($supplier['email1'], $supplier['name']);
            if ($supplier['email2']){
            	$mail->addTo($supplier['email2'], $supplier['name']);
			}
			if ($settings['bcc_address_email']){
				$mail->addBcc($settings['bcc_address_email']);
			}
            $mail->setBodyHtml($finalemail,'utf-8');
			
			if ($supplier['pdf_enabled'] == 1){
				$orderIncrementId = $order->getRealOrderId();	
				$at = $mail->createAttachment($this->getPdf($order_id,$supplier_id,$items));
				$at->type        = 'application/pdf';
				$at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
				$at->encoding = Zend_Mime::ENCODING_BASE64;
				$at->filename    = "order_" . $orderIncrementId . ".pdf";
			}  
			
			$subject = $settings['email_subject'];		
			$senderName  = Mage::getStoreConfig('supplier/emailoptions/email_sender_name');
			$senderEmail = Mage::getStoreConfig('supplier/emailoptions/email_sender_email');
            $mail->setFrom($senderEmail, $senderName);
            $mail->setSubject($this->processSubject($subject, $order));
            if($settings['smtp_enabled']==1){
				$config = $this->getSmtp($order->getStoreId());	
				$transport = new Zend_Mail_Transport_Smtp($settings['smtp_server'], $config);
				$mailFlag = $mail->send($transport);
			} else {
            	$mailFlag = $mail->send();
			}
			return true;
		}

		public function getCsv($order_id,$supplier_id,$items,$trigger){
			$order = Mage::getModel('sales/order')->load($order_id);
			$supplier = Mage::getModel('supplier/supplier')->load($supplier_id)->getData();
			$settings = $this->getSettings($order->getStoreId());
			$orderIncrementId = $order->getRealOrderId();
			
			$finalcsv = $this->processTemplate($order,$supplier,$items,'csv');
			$templates = $this->getTemplates($supplier,null); 
			$header = explode('||',$templates['csv']);
			
			Mage::getModel('supplier/observer')->logging($header);
			Mage::getModel('supplier/observer')->logging($finalcsv);
			
			$csvPath = '/var/export/supplier/' . $supplier['id'];
		  	if (!is_dir(BP . '/var/export/supplier/' . $supplier['id'])) {						
				if (!mkdir(BP . $csvPath, 0777)) {
					Mage::getModel('supplier/observer')->logging('could not create folder');
				}
			}
			$path = BP . $csvPath;
			
			if($supplier['csv_delimeter']){
				$delimeter = $supplier['csv_delimeter'];  	
			} else {
				$delimeter = "|"; 	
			}
			
			if($supplier['xml_type']==0 || !$supplier['xml_type'] || $supplier['schedule_enabled']==2){
					if(file_exists($path . '/'.$supplier['xml_name'].'.csv')){
						$fp = fopen($path . '/'.$supplier['xml_name'].'.csv', 'a');
					} else {
						$fp = fopen($path . '/'.$supplier['xml_name'].'.csv', 'w');
						if(!empty($header)){
							fputcsv($fp, $header,$delimeter,'"');
						}
					}
					foreach ($finalcsv as $fields) {
    					fputcsv($fp, $fields,$delimeter,'"');
					}
					fclose($fp); 
				}
			elseif ($supplier['xml_type']==1) {	
				$fp = fopen($path . '/'.$supplier['xml_name'].'_' . $orderIncrementId . '.csv', 'w');
				if(!empty($header)){
					fputcsv($fp, $header,$delimeter,'"');
				} 
				foreach ($finalcsv as $fields) {
    				fputcsv($fp, $fields,$delimeter,'"');
				}
				fclose($fp);
			}
			
			if($supplier['xml_type']==0 || $supplier['schedule_enabled']==2){
				$file = $path . '/' . $supplier['xml_name'] . '.csv';
			} 
			elseif($supplier['xml_type']==1){
				$file = $path . '/' . $supplier['xml_name'] . '_' . $orderIncrementId . '.csv';
			} 

			Mage::getModel('supplier/observer')->logging($fp);

			if ($supplier['xml_ftp']==1){
				$ftp = $this->UploadToFtp($file,$supplier,$trigger,$orderIncrementId,'csv',$order);	
				if($ftp){
					return true;	
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
		
		public function getXML($order_id,$supplier_id,$items,$trigger){
				$order = Mage::getModel('sales/order')->load($order_id);
				$supplier = Mage::getModel('supplier/supplier')->load($supplier_id)->getData();
				$settings = $this->getSettings($order->getStoreId());
				$orderIncrementId = $order->getRealOrderId();
	
			  	Mage::getModel('supplier/observer')->logging("XML Create");
				
				// Check XML folder
				$xmlPath = '/var/export/supplier/' . $supplier['id'];
			  	if (!is_dir(BP . '/var/export/supplier/' . $supplier['id'])) {						
					if (!mkdir(BP . $xmlPath, 0777)) {
						Mage::getModel('supplier/observer')->logging('could not create folder');
					}
				}
				$path = BP . $xmlPath;
			
				// Check XML file
				
				if($supplier['xml_type']==0 || $supplier['schedule_enabled']==2){
					$file = $path . '/' . $supplier['xml_name'] . '.xml';
				} 
				elseif($supplier['xml_type']==1){
					$file = $path . '/' . $supplier['xml_name'] . '_' . $orderIncrementId . '.xml';
				}  
				
				// Load File 
				$xmlRead = fopen($file, 'r');
				$xmlReadData = fread($xmlRead, filesize($file));
				$xmlWrite = fopen($file, 'w+');
				
				Mage::getModel('supplier/observer')->logging($file);	
				Mage::getModel('supplier/observer')->logging(filesize($file));
				
				// Set header, open & close is file is new or empty
				if (!is_file($file) || !$xmlReadData || $supplier['xml_type']==1) {
					$templateXML = $this->processTemplate($order,$supplier,$items,'xmlnew');
					Mage::getModel('supplier/observer')->logging('xml new');	
				} else {
					$templateXML = $this->processTemplate($order,$supplier,$items,'xml');
					Mage::getModel('supplier/observer')->logging('xml'); 	
				}

				Mage::getModel('supplier/observer')->logging($templateXML);
				
				if($supplier['xml_type']==0 || $supplier['xml_type']=='' || $supplier['schedule_enabled']==2){				
					// XML Single File				
					Mage::getModel('supplier/observer')->logging("XML Single File");				
					if (!is_file($file) || !$xmlReadData){
						fwrite($xmlWrite, $templateXML);
					} else {
						$xml_close = '</orderxml>';
						$xml_replaced = str_replace($xml_close, $templateXML . $xml_close, $xmlReadData);
						fwrite($xmlWrite, $xml_replaced);
					}			
					fclose($xmlRead);
					fclose($xmlWrite);				
				}
				
				elseif($supplier['xml_type']==1){
					// XML file per order
					Mage::getModel('supplier/observer')->logging("XML file per order");
					fwrite($xmlWrite, $templateXML);
					fclose($xmlRead);
					fclose($xmlWrite);
				} 
				
				if ($supplier['xml_ftp'] == 1){
					// Upload file
					$ftp = $this->UploadToFtp($file,$supplier,$trigger,$orderIncrementId,'xml',$order);
					
					if($ftp){
						return true;	
					} else {
						return false;
					}
				} else {
					return true;
				}
		}

		private function getTemplates($supplier,$force){					
			$email_template_id = $supplier['email_template'];
			$xml_template_id = $supplier['xml_template'];
			$pdf_template_id = $supplier['pdf_template'];	
			
			$templates['pdfreport'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/pdf/report.phtml')->toHtml();
			$templates['pdfreportitems'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/pdf/reportitems.phtml')->toHtml();
			
			if($pdf_template_id=="" || $pdf_template_id=="0" || $force=='base'){
				$templates['pdf'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/pdf/template.phtml')->toHtml();	
				$templates['pdfitems'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/pdf/items.phtml')->toHtml();
			} else {
				$mailTemplate = Mage::getModel('supplier/templates')->load($pdf_template_id)->getData();
				$templates['pdf'] = $mailTemplate['body'];
				$templates['pdfitems'] = $mailTemplate['item'];
			}
			
			if($email_template_id=="" || $email_template_id=="0" || $force=='base'){
				$templates['email']  = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/email/template.phtml')->toHtml();
				$templates['emailitems'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/email/items.phtml')->toHtml();
			}else{
				$mailTemplate = Mage::getModel('supplier/templates')->load($email_template_id)->getData();
				$templates['email'] = $mailTemplate['body'];
				$templates['emailitems'] = $mailTemplate['item'];
			}
			if($xml_template_id=="" && $supplier['xml_csv'] == 0 || $xml_template_id=="0" && $supplier['xml_csv'] == 0 || $force=='base'){
				$templates['xml'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/xml/template.phtml')->toHtml();
				$templates['xmlitems'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/xml/items.phtml')->toHtml();
			}else{
				$xmlTemplate = Mage::getModel('supplier/templates')->load($xml_template_id)->getData();
				$templates['xml'] = $xmlTemplate['body'];
				$templates['xmlitems'] = $xmlTemplate['item'];
			}
			if($xml_template_id=="" && $supplier['xml_csv'] == 1 || $xml_template_id=="0" && $supplier['xml_csv'] == 1 || $force=='base'){
				$templates['csv'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/csv/template.phtml')->toHtml();
				$templates['csvitems'] = Mage::app()->getLayout()->createBlock('supplier/template')->setData('area','frontend')->setTemplate('supplier/csv/items.phtml')->toHtml();
			} elseif($supplier['xml_csv'] == 1) { 
				$xmlTemplate = Mage::getModel('supplier/templates')->load($xml_template_id)->getData();
				$templates['csv'] = $xmlTemplate['body'];
				$templates['csvitems'] = $xmlTemplate['item'];
			}
			return $templates;
		}
		
		private function getSettings($store_id){
			$settings = Mage::getModel('supplier/supplier')->getSupplierSettings($store_id);
			return $settings;
		}
		
		private function processSubject($subject,$order){
			$variables = array('order'=>$order);	
			$processor = Mage::helper('cms')->getBlockTemplateProcessor();		
			$processor->setVariables($variables);				
			return $processor->filter($subject);		
		}
		
		private function processTemplate($order,$supplier,$items,$type){
			$templates = $this->getTemplates($supplier,null);		
			$settings = $this->getSettings($order->getStoreId());
			$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
			$customer = $customer->getData();
			foreach($customer as $key => $value ){ $customerarray['customer_' . $key] = $value; }
			
			if($order->getIsVirtual()!=1){
				$shippingaddress = $order->getShippingAddress()->getData();
				foreach($shippingaddress as $key => $value ){ $shippingarray['shipping_' . $key] = $value; }
			}
			
			$billingaddress = $order->getBillingAddress()->getData();
			foreach($billingaddress as $key => $value ){ $billingarray['billing_' . $key] = $value; }
			
			if($supplier['email_header']){
				$emailHeader = $supplier['email_header'];
			} else {
				$emailHeader = $settings['message_header'];
			}
			
			if($supplier['email_message']){
				$emailMessage = $supplier['email_message'];
			} else {
				$emailMessage = $settings['message_for_supplier'];
			}
			
			if($supplier['pdf_header']){
				$pdfHeader = $supplier['pdf_header'];
			} else {
				$pdfHeader = $settings['pdf_header'];
			}
			
			if($supplier['pdf_message']){
				$pdfMessage = $supplier['pdf_message'];
			} else {
				$pdfMessage = $settings['pdf_for_supplier'];
			}
			
			if(isset($settings['filename'])){
				$supplierImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/supplier/' . $settings['filename'];
			} else {
				$supplierImage = '';
			}
			
			if(isset($settings['pdffilename'])){
				$supplierImagePDF = '<img src="media/supplier/' . $settings['pdffilename'] . '"/>';
			} else {
				$supplierImagePDF = '&nbsp;';
			}
			
			
			$templatePDFitems_processed = $this->processitemTemplate($templates['pdfitems'],$items,$type,null,$supplier);
			$templateEmailitems_processed = $this->processitemTemplate($templates['emailitems'],$items,$type,null,$supplier);
			$templateXMLitems_processed = $this->processitemTemplate($templates['xmlitems'],$items,$type,null,$supplier);
			
			if($order->getGiftMessageId()){
				$giftmessage = Mage::getModel('giftmessage/message')->load($order->getGiftMessageId())->getData();
				$giftmessagearray = array();
				foreach($giftmessage as $key=>$value){
					$giftmessagearray['order_giftmessage_'.$key] = $value; 
				}
			} 
			
			$variables = array(
				'order' => $order,
				'logo_pdf' =>  $supplierImagePDF,
				'logo_email' => $supplierImage,
				'supplier' => $supplier,
				'dropship_date' => date('D, d M Y H:i:s', time()),
				'email_header' => $emailHeader,
				'email_message' => $emailMessage,
				'email_items'=> $templateEmailitems_processed,
				'pdf_header' => $pdfHeader,
				'pdf_message' => $pdfMessage,
				'pdf_items'=> $templatePDFitems_processed,
				'xml_items'=> $templateXMLitems_processed,								
			);
			
			if(!empty($shippingarray)){
				$addresses = array_merge($shippingarray, $billingarray);
			} else {
				$addresses = $billingarray;	
			}
			
			$variables = array_merge($variables, $addresses );
			
			if(!empty($giftmessagearray)){ $variables = array_merge($variables, $giftmessagearray);}
			if(!empty($customerarray)){ $variables = array_merge($variables, $customerarray); }	
			
			foreach($supplier as $key => $value ){ $variables['supplier_' . $key] = $value; }
			
			$processor = Mage::helper('cms')->getBlockTemplateProcessor();	
		
			if($type=='pdf'){
				$processor->setVariables($variables);				
				$finaltemplate = $processor->filter($templates['pdf']);		
			} elseif($type=='email') {
				$processor->setVariables($variables);
				$finaltemplate = $processor->filter($templates['email']);				
			} elseif($type=='xml'){
				$processor->setVariables($variables);							
				$finaltemplate = $processor->filter($templates['xml']);		
			} elseif($type=='xmlnew'){
				$xml_header = '<?xml version="1.0" encoding="UTF-8"?>';
				$xml_open= '<orderxml>';
				$xml_close = '</orderxml>';
				$xml_filter_options = array( 
					'xml_header' => $xml_header,
					'xml_open' => $xml_open,
					'xml_close' => $xml_close
					);
				$variables = array_merge($variables, $xml_filter_options);
				$processor->setVariables($variables);
				$finaltemplate = $processor->filter($templates['xml']); 
			} elseif($type=='csv'){ 
				$finaltemplate = $this->processitemTemplate($templates['csvitems'],$items,$type,$variables,$supplier);
			}		
			return $finaltemplate;
			
		}
		
		private function processitemTemplate($template,$items,$type,$templatevariables,$supplier){	
			$templates = $this->getTemplates($supplier,null);
			$finaltemplate = '';	
							
			foreach($items as $item){
				$product = Mage::getModel('catalog/product')->load($item->getProductId());				
				$customOptionsInfo = Mage::getModel('supplier/attributes')->getCustomOptions($item);
			    $xmlcustomOptionsInfo = Mage::getModel('supplier/attributes')->getCustomOptionsXml($item);
				$allProductAttributes = Mage::getModel('supplier/attributes')->getAllAttributes($product);
				$order = Mage::getModel('sales/order')->load($item->getOrderId());
				$settings = $this->getSettings($order->getStoreId());
				
				if($item->getGiftMessageId()){
					$giftmessage = Mage::getModel('giftmessage/message')->load($item->getGiftMessageId())->getData();
					$giftmessagearray = array();
					foreach($giftmessage as $key=>$value){
						$giftmessagearray['item_giftmessage_'.$key] = $value; 
					}
				}
				
				if($settings['email_image_size']){
					$defaultImageSize = $settings['email_image_size'];
				} else {
					$defaultImageSize = 100;
				} 
				
				if($settings['pdf_image_size']){
					$defaultImageSizePdf = $settings['pdf_image_size'];
				} else {
					$defaultImageSizePdf = 100;
				} 
							
				$relativePath = 'media/catalog/product'.$product->getImage();
				$relativePathNotFound = 'skin/frontend/base/default/images/catalog/product/placeholder/thumbnail.jpg';

				if(file_exists(BP . DS . $relativePath)){				
					$productImagePdf = '<img src="' . $relativePath . '" width="'.$defaultImageSizePdf.'"/>';
					$productImage = '<img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $relativePath .'" width="'.$defaultImageSize.'"/>';  
					$productImageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $relativePath;
				} else {
					$productImagePdf = '<img src="' . $relativePathNotFound . '" width="'.$defaultImageSizePdf.'"/>';
					$productImage = '<img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $relativePathNotFound .'" width="'.$defaultImageSize.'"/>';
					$productImageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). $relativePathNotFound;
				} 
			
				$variables = array(
					'product_image_url'=>$productImageUrl,
					'product_image' => $productImage,
					'product_image_pdf' => $productImagePdf,
					'orderid' => $order->getRealOrderId(),
					'sku' => $item->getSku(),
					'product_name' => $item->getName(),
					'qty' => $item->getQtyOrdered() - $item->getQtyRefunded(),
					'options' => $customOptionsInfo,
					'xml_options' => $xmlcustomOptionsInfo,
					'finalprice' => $item->getPrice() * $item->getQtyOrdered() 			 
				);	 

				if(!empty($giftmessagearray)){ $variables = array_merge($variables, $giftmessagearray);}

				$final_variables = array_merge($variables, $allProductAttributes);
				
				if($type=='csv' && $templatevariables){
					$final_variables = array_merge($final_variables,$templatevariables);
				}
		
				$processor = Mage::helper('cms')->getBlockTemplateProcessor();	
				$processor->setVariables($final_variables);
				
				if($type=='csv'){
					$attributes = explode('||', $processor->filter($template));
					$finaltemplate[] = $attributes;
				} else {
					$finaltemplate .= $processor->filter($template);	
				}
			}
			
			return $finaltemplate;
			
		}

		private function UploadToFtp($file,$supplier,$type,$orderIncrementId,$output,$order){
		
			$settings = $this->getSettings($order->getStoreId());
			
			if($supplier['xml_enabled']==1 && $supplier['xml_ftp']==1 && $supplier['xml_ftp_host'] && $supplier['xml_ftp_user']){
					
			Mage::getModel('supplier/observer')->logging('FTP XML | Start Process');
			
			$host = $supplier['xml_ftp_host'];
			$usr = $supplier['xml_ftp_user'];
			$pwd = $supplier['xml_ftp_password'];
			$port = $supplier['xml_ftp_port'];
			 
			// file to move:
			$local_file = $file;
			
			if($supplier['schedule_enabled']==2 || $supplier['xml_type']==0){
				$date = date("Ymd_His");
				$ftp_path = $supplier['xml_ftp_path'] . $supplier['xml_name'] . '_' . $date . '.' .$output; 
			} else {
				$ftp_path = $supplier['xml_ftp_path'] . $supplier['xml_name'] . '_' . $orderIncrementId . '.' .$output;
			}
					
			// connect to FTP server (port 21)
			$conn_id = ftp_connect($host, $port); // die ("Cannot connect to host");
			
			if($conn_id){ 
				Mage::getModel('supplier/observer')->logging("FTP XML | Connected"); 
			} else { 
				Mage::getModel('supplier/observer')->logging("FTP XML | Failed to Connect");
				if($type=='manual'){ Mage::getSingleton('adminhtml/session')->addError("XML FTP | Failed to Connect"); }	 
			}
			 
			// send access parameters
			$login_result = ftp_login($conn_id, $usr, $pwd); //or die("Cannot login");
			
			if($login_result){ 
				Mage::getModel('supplier/observer')->logging("FTP XML | Logged in"); 
			} else { 
				Mage::getModel('supplier/observer')->logging("FTP XML | Failed to log in"); 
				if($type=='manual'){ Mage::getSingleton('adminhtml/session')->addError("XML FTP | Failed to log in"); }
				$ftpstatus = 'failed'; 	
			}
			 
			// turn on passive mode transfers (some servers need this)
			if($supplier['xml_ftp_type']==1){
			 	ftp_pasv($conn_id, true);
				Mage::getModel('supplier/observer')->logging("FTP XML | Passive mode");
			}
			
			// perform file upload
			$upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_ASCII);
			 
			// check upload status:
			
			if(!$upload){ 
				Mage::getModel('supplier/observer')->logging("FTP XML | Failed to upload"); 
				if($type=='manual'){ Mage::getSingleton('adminhtml/session')->addError("XML FTP | Failed to upload"); }
				$ftpstatus = 'failed'; 
			} else {
				Mage::getModel('supplier/observer')->logging("FTP XML | Succesfully uploaded");
			}
			 
			 ftp_close($conn_id);
			 
			 if($ftpstatus=="failed"){
				
				if($settings['ftp_mail_enabled']==1){
					Mage::getModel('supplier/observer')->logging("FTP XML | Failed Email Send");
					
					$emailerror = 'The following file could no be uploaded <br/>' . $file; 
					$mail = new Zend_Mail();
					$mail->addTo($settings['ftp_error_email'],$settings['ftp_error_email_name']);
					$mail->setSubject('FTP upload error');
					$mail->setBodyHtml($emailerror,'utf-8');
					$mail->setFrom($settings['ftp_error_email'], $settings['ftp_error_email_name']);
					$mail->send(); 
				}
				
			 } else {
				
				 Mage::getModel('supplier/observer')->logging('FTP XML | Start Moving File');	
				 // Move File to Archive/Uploaded Folder
				 if (!is_dir(BP . '/var/export/supplier/' . $supplier['id'] . '/uploaded')) {
											
					 if (!mkdir(BP . '/var/export/supplier/' . $supplier['id'] . '/uploaded', 0777)) {
								Mage::getModel('supplier/observer')->logging('FTP XML | Could not create folder');
					 } 
				 }
				 
				 $fileArchive = BP . '/var/export/supplier/' . $supplier['id'] . '/uploaded/uploaded_' . $ftp_path;
				 
				 if(!rename($file,$fileArchive)){
					 Mage::getModel('supplier/observer')->logging('FTP XML | Could not move or rename file');			
				 } else {
					 Mage::getModel('supplier/observer')->logging('FTP XML | File Moved');
					 return true;	 
				 }
				 
			 }
	 
			} else {
				if($type=='manual'){ Mage::getSingleton('adminhtml/session')->addError("XML FTP | Ftp settings are not complete"); }
				else { Mage::getModel('supplier/observer')->logging('FTP XML | Could not move or rename file'); }
			}
			
		}
	 	private function getSmtp($storeId){
	 		$settings = $this->getSettings($storeId);
	 		$port = $settings['smtp_port'];
			$auth = "login";
			$username = $settings['smtp_username'];
			$password = $settings['smtp_password'];
			if(!$port){
				$port = 25;
			}
	 		$smtp = array(
	 			/*'ssl' => 'tls',*/ 
	 			'port' => $port, 
	 			'auth' => $auth, 
	 			'username' => $username, 
	 			'password' => $password
			);
			if($settings['smtp_ssl']){
				$smtp['ssl'] = 'tls';
			}
			//mage::log($smtp,null,'smtp.log');
	 		return $smtp;
	 	}
		
}
	