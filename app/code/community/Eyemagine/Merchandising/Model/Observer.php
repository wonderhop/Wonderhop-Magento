<?php
/**
 * EyeMagine - The leading Magento Solution Partner.
 *
 * @author     EyeMagine <magento@eyemaginetech.com>
 * @category   Eyemagine
 * @package    Eyemagine_Merchandising
 * @copyright  Copyright (c) 2003-2012 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html (GPL)
 */
 
class Eyemagine_Merchandising_Model_Observer
{
    /**
     * Add Merchandising Tab
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function addMerchandisingTab(Varien_Event_Observer $observer)
    {
		$tabs = $observer->getTabs();
	    $tabs->addTab('merchandising', array(
            'label'     => Mage::helper('catalog')->__('Merchandising'),
            'content'   => $tabs->getLayout()->createBlock('adminhtml/catalog_category_tab_merchandising', 'category.product.merchandising.grid')->toHtml(),
        ));
		
		return $tabs;
	}
}