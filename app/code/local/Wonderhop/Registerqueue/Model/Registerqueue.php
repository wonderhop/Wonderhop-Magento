<?php

class Wonderhop_Registerqueue_Model_Registerqueue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('registerqueue/registerqueue');
    }
}
