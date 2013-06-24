<?php

class MagentoMasters_Supplier_Model_Calculateoptions {
    public function toOptionArray()
    {
        return array(
                        array(
                                'value'     => '1',
                                'label'     => Mage::helper('supplier')->__('Total shipping cost of all suppliers'),
                        ),
                        array(
                                'value'     => '2',
                                'label'     => Mage::helper('supplier')->__('Most Expensive shippingcost only'),
                        ),
						array(
                                'value'     => '3',
                                'label'     => Mage::helper('supplier')->__('Fixed Price Per Supplier. Free Shipment Above Set Amount'),
                        ),
        );
    }
}