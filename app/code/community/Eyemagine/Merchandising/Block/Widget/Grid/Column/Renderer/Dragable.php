<?php
/**
 * Grid column renderer
 * renders Position column
 *
 * EyeMagine - The leading Magento Solution Partner.
 * 
 * @author     EyeMagine <magento@eyemaginetech.com>
 * @category   Eyemagine
 * @package    Eyemagine_Merchandise
 * @copyright  Copyright (c) 2003-2012 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html (GPL)
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
