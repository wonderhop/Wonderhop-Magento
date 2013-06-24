<?php

class MagentoMasters_Supplier_Model_Showaddresstype {
    public function toOptionArray()
    {
        return array(
                        array(
                                'value'     => 'sh',
                                'label'     => Mage::helper('supplier')->__('Shipping'),
                        ),
                        array(
                                'value'     => 'b',
                                'label'     => Mage::helper('supplier')->__('Billing'),
                        ),
                        array(
                                'value'     => 'both',
                                'label'     => Mage::helper('supplier')->__('Both'),
                        )
        );
    }
}