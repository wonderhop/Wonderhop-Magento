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

class Eyemagine_Cloudsponge_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function popupAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Get contacts in JSON format.
     */
    public function getContactsJSONAction()
    {
    	$importId = Mage::helper('cloudsponge')->getImportId();
    	
    	// if the import ID is empty, do nothing
    	if (empty($importId)) {
    		exit;
    	}
    	
    	$contacts = Mage::helper('cloudsponge/data')->retrieveContacts($importId, 2000);
    	
    	// if there are contacts, return them and unset the import ID
    	if (!empty($contacts)) {
    		
	    	echo Mage::helper('core')->jsonEncode($contacts);
    		Mage::helper('cloudsponge')->setImportId(0);
	    	exit;
    	}
    }
    
    /**
     * Get error message
     */
    public function getErrorMessageAction()
    {
    	$importId = Mage::helper('cloudsponge')->getImportId();

    	// if the import ID is empty, do nothing
    	if (empty($importId)) {
    		exit;
    	}
    	
    	$errorMessage = Mage::helper('cloudsponge/data')->getErrorMessage($importId);
    	
    	// if there is an error, return it and unset the import ID
    	if (!empty($errorMessage)) {
    		
	    	echo $this->__($errorMessage);
    		Mage::helper('cloudsponge')->setImportId(0);
	    	exit;
    	}
    }
}
