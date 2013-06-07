<?php

/**
 * EYEMAGINE - The leading Magento Solution Partner
 *
 * Merchandising Made Easy
 *
 * @package Eyemagine_Merchandising
 * @author EYEMAGINE <support@eyemaginetech.com>
 * @category Eyemagine
 * @copyright Copyright (c) 2013 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *
 */

class Eyemagine_Merchandising_Block_Widget_Grid_Column_Renderer_Dragable extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
		return '<img src="'.$this->getSkinUrl('images/icon-drag.png').'" style="cursor:move" alt="Drag and drop row." title="Drag and drop row."/> &nbsp; 
				<img src="'.$this->getSkinUrl('images/icon-to-top.png').'" style="cursor:hand" class="movetotop" alt="Move this row to the top." title="Move this row to the top." id="move2top_'.$row->getId().'" />';
    }
}