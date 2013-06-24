<?php
class Magentomasters_Supplier_Model_Entity_Attribute_Backend_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
	public function getAllOptions()
    {
       		$suppliers = Mage::getModel('supplier/supplier')->getCollection();
       		$this->_options = array();
       		foreach ($suppliers as $s)
       		{
       			$this->_options[] = array(
       					'value' => $s->getData('id'). $s->getName(),
       					'label' => $s->getName()
       					);
       		}
        
        return $this->_options;
    }
}