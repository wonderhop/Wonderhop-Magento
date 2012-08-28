<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    OSL
 */

class J2t_Ajaxcheckout_Model_Transparency
{
    public function toOptionArray()
    {
        $return_value = array(
            array('value' => '0', 'label'=>Mage::helper('j2tajaxcheckout')->__('100%')),
            array('value' => '0.1', 'label'=>Mage::helper('j2tajaxcheckout')->__('90%')),
            array('value' => '0.2', 'label'=>Mage::helper('j2tajaxcheckout')->__('80%')),
            array('value' => '0.3', 'label'=>Mage::helper('j2tajaxcheckout')->__('70%')),
            array('value' => '0.4', 'label'=>Mage::helper('j2tajaxcheckout')->__('60%')),
            array('value' => '0.5', 'label'=>Mage::helper('j2tajaxcheckout')->__('50%')),
            array('value' => '0.6', 'label'=>Mage::helper('j2tajaxcheckout')->__('40%')),            
            array('value' => '0.7', 'label'=>Mage::helper('j2tajaxcheckout')->__('30%')),
            array('value' => '0.8', 'label'=>Mage::helper('j2tajaxcheckout')->__('Default value (20%)')),
            array('value' => '0.9', 'label'=>Mage::helper('j2tajaxcheckout')->__('10%')),
            array('value' => '1', 'label'=>Mage::helper('j2tajaxcheckout')->__('0%'))
        );
        
        return $return_value;
    }
}
