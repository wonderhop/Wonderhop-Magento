<?php
/**
 * EYEMAGINE - The leading Magento Solution Partner
 *
 * @author     EYEMAGINE <magento@eyemaginetech.com>
 * @category   Eyemagine
 * @package    Eyemagine_Cloudsponge
 * @copyright  Copyright (c) 2003-2012 EyeMagine Technology, LLC (http://www.eyemaginetech.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html (GPL)
 */
class Eyemagine_Cloudsponge_Model_Mysql4_Cloudsponge extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('cloudsponge/cloudsponge', 'cloudsponge_id');
    }
}