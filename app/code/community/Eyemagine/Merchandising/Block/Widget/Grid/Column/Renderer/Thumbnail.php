<?php

/**
 * EYEMAGINE - The leading Magento Solution Partner
 *
 * MME Grid column renderer
 * renders Thumbnail column
 *
 * @package Eyemagine_Merchandising
 * @author EYEMAGINE <support@eyemaginetech.com>
 * @category Eyemagine
 * @copyright Copyright (c) 2013 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *
 */

class Eyemagine_Merchandising_Block_Widget_Grid_Column_Renderer_Thumbnail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected $_values;

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
		$imgUrl = Mage::helper('catalog/image')->init($row, 'image')->resize(75, 75);
		
		return "<img src=\"$imgUrl\" />";
    }
}