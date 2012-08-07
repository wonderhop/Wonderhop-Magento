<?php

class Wonderhop_Invitations_Model_Mysql4_Invitations extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
       
        $this->_init('invitations/invitations', 'invitation_id');
    }
}
