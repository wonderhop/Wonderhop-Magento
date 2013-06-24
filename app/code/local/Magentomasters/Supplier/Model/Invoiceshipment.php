<?php

class MagentoMasters_Supplier_Model_Invoiceshipment {
    public function toOptionArray()
    {
        return array(
            array('value' => 'invoice', 'label'=>Mage::helper('adminhtml')->__('Invoice')),
            array('value' => 'manual', 'label'=>Mage::helper('adminhtml')->__('Manual')),
			array('value' => 'invoicemanual', 'label'=>Mage::helper('adminhtml')->__('Invoice and Manual')),
			array('value' => 'ordercreate', 'label'=>Mage::helper('adminhtml')->__('Order Create')),
        );
    }
}