<?php

class Wonderhop_Registerqueue_Model_Mysql4_Registerqueue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('registerqueue/registerqueue');
    }
}
