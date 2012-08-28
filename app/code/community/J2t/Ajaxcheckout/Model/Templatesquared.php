<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    OSL
 */

class J2t_Ajaxcheckout_Model_Templatesquared
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'squared', 'label'=>Mage::helper('j2tajaxcheckout')->__('Squared theme (not compatible IE6.5)'))
        );
    }

    public function getCssName()
    {
        return 'ajax_cart_template_squared.css';
    }

    public function getWH()
    {
        return 20;
    }

}
