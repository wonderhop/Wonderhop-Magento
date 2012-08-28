<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    OSL
 */

class J2t_Ajaxcheckout_Model_Otherlist
{
    public function toOptionArray()
    {
        $return_value = array(
            array('value' => 'up-sells', 'label'=>Mage::helper('j2tajaxcheckout')->__('Up-sells')),
            array('value' => 'cross-sells', 'label'=>Mage::helper('j2tajaxcheckout')->__('Cross-sells')),
            array('value' => 'related-products', 'label'=>Mage::helper('j2tajaxcheckout')->__('Related products'))
        );
        
        
        return $return_value;
    }
}
