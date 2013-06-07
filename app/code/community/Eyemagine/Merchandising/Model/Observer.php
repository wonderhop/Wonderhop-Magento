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