<?php
class Magentomasters_Supplier_Model_Mysql4_Templates extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("supplier/templates", "id");
    }
}