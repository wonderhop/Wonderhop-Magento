<?php

class Wonderhop_Invitations_Model_Mysql4_Invitations_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('invitations/invitations');
    }
}
