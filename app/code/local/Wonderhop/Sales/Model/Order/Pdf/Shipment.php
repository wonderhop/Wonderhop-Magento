<?php

class Wonderhop_Sales_Model_Order_Pdf_Shipment extends Mage_Sales_Model_Order_Pdf_Shipment {
    
    
    
    public function addGiftMsg($page, $giftMessageSender, $giftMessageNote)
    {
        if(empty($giftMessageNote)) {
            return;
        }
        $pipfmText = $giftMessageSender ."***BREAK***"."  "."***BREAK***".wordwrap($giftMessageNote, 100, "***BREAK***", true);
        $pipfmTextLines = array();
        $pipfmTextLines = explode("***BREAK***", $pipfmText);
        $i = 0;
        $pipfmTextLineStartY = 300;
        foreach ($pipfmTextLines as $pipfmTextLine)
        {
            $i ++;
            //Bold only the first line
            if($i == 1)
            {
                //$this->_setFontBold_Modified($page, 10);
                $this->_setFontBold($page, 10);
            } else {
                $this->_setFontRegular($page, 10);
            }
            $page->drawText($pipfmTextLine, 60, $pipfmTextLineStartY, 'UTF-8');
            $pipfmTextLineStartY = $pipfmTextLineStartY - 10;
            break;
        }
    }
    
    
    public function getPdf($shipments = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
                Mage::app()->setCurrentStore($shipment->getStoreId());
            }
            $page  = $this->newPage();
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId()
            );
            /* Add table */
            //$this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            // ORIGINAL CODE REMOVED FOR READABILITY PURPOSES
            $giftMessage = Mage::getModel("giftmessage/message")->load($order->getGiftMessageId());
            if ($giftMessage and $giftMessage->getId()) {
                $giftMessageSender ="Message from ".$giftMessage->getSender().':';
                $giftMessageNote = $giftMessage->getMessage();
                $this->addGiftMsg($page, $giftMessageSender, $giftMessageNote);
            }
        }
        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }
}
