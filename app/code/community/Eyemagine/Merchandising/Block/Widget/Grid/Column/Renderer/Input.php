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
class Eyemagine_Merchandising_Block_Widget_Grid_Column_Renderer_Input extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
        if($row->getData($this->getColumn()->getIndex())) {
			$posVal = $row->getData($this->getColumn()->getIndex());
		} else {
			$posVal =  0;
		}
		
		$html = $posVal;
		$html.= '<input style="display:none" type="text" ';
		$html.= 'name="'.($this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId()).'" ';
		$html.= 'value="'.$posVal.'"';
		$html.= 'class="input-text '.$this->getColumn()->getInlineCss().'"/>'.$this->getColumn()->getName();
        return $html;
	}
}
