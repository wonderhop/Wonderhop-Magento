<?php

class Wonderhop_Invitations_Model_Invitations extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('invitations/invitations');
    }
}
