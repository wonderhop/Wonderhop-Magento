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

include_once 'lib/cloudsponge/csimport.php';
class Eyemagine_Cloudsponge_Helper_Data extends Mage_Core_Helper_Abstract
{
	const CONFIG_PATH_ENABLED = 'cloudsponge/account/enabled';
	const CONFIG_PATH_DOMAIN_KEY = 'cloudsponge/account/domain_key';
	const CONFIG_PATH_DOMAIN_PASSWORD = 'cloudsponge/account/domain_password';
	
	public function __construct()
	{
		if (!defined('DOMAIN_KEY')) {
			define('DOMAIN_KEY', Mage::getStoreConfig(self::CONFIG_PATH_DOMAIN_KEY));
		}
		
		if (!defined('DOMAIN_PASSWORD')) {
			define('DOMAIN_PASSWORD', Mage::getStoreConfig(self::CONFIG_PATH_DOMAIN_PASSWORD));
		}
	}
	
	/**
	 * Initiate the import process.  Call the API begin_import() method.
	 * 
	 * @param str $service Possible optoins: yahoo, google, aol, plaxo, apple
	 * @param str $username
	 * @param str $password
	 * @return int Import ID
	 */
	public function initiateImport($service, $username = null, $password = null)
	{
		// Call to the CloudSponge.com for the import_id and redirect url (if applicable)
		$output = CSImport::begin_import($service, $username, $password, NULL);

		if (isset($output['import_id'])) {
    
			if (!empty($output['consent_url'])) {

				Mage::register('consent_url', ($output['consent_url']));
    
			} else if (!empty($output['applet_tag'])) {

				Mage::register('applet_tag', $output['applet_tag']);
			}
				
			$this->setImportId($output['import_id']);
			return $output['import_id'];
		}
	}
	
	/**
	 * Is the CloudSponge module enabled?
	 * 
	 * @return int
	 */
	public function isEnabled()
	{
		return Mage::getStoreConfig(self::CONFIG_PATH_ENABLED);
	}
	
	/**
	 * Return applet tag
	 * 
	 * @return str
	 */
	public function getAppletTag()
	{
		return Mage::registry('applet_tag');
	}
	
	/**
	 * Return the consent URL from the registry
	 * 
	 * @return str
	 */
	public function getConsentUrl()
	{
		return Mage::registry('consent_url');
	}
	
	/**
	 * Store the import ID as a cookie
	 * 
	 * @param int $importId
	 * @return Mage_Core_Model_Cookie 
	 */
	public function setImportId($importId)
	{
		Mage::register('import_id', $importId);
		
		// if the import ID is empty, delete the cookie
		if (empty($importId)) {
			
			return Mage::getModel('core/cookie')->delete('cloudsponge_import_id');
		}
		
		return Mage::getModel('core/cookie')->set('cloudsponge_import_id', $importId);
	}
	
	/**
	 * Retrieve the import ID cookie value
	 * 
	 * @return int 
	 */
	public function getImportId()
	{
		return Mage::getModel('core/cookie')->get('cloudsponge_import_id');
	}
	
	/**
	 * Retrieve contacts from CloudSponge API
	 * 
	 * @param int $importId
	 * @param int $timeout
	 * @return array
	 */
	public function retrieveContacts($importId, $timeout)
	{
		$contactsResult = CSImport::get_contacts($importId);
		$contacts = $contactsResult['contacts'];
		$contactsOwner = $contactsResult['contacts_owner'];
	  
		if (!empty($contacts)) {
			
			foreach ($contacts as $contact) {
				
				$contactEmail = $contact->email();
				
				// if there is a single email for the name, add it to the array
				if (!empty($contactEmail)) {
					
					$contactFormatted[] = array(
						'name' => trim($contact->name()), 
						'email' => trim($contactEmail)
					);
				}				
			}
			
			return $contactFormatted;
	  	}
	  	
	  	return array();
	}
	
	/**
	 * Get the error message if there is one
	 * 
	 * @param int $importId
	 * @return str
	 */
	public function getErrorMessage()
	{
		$importId = $this->getImportId();
	  	$events = CSImport::get_events($importId);

	  	foreach ($events as $event) {

		  	// look for an error event 
		  	if ($event['status'] == 'ERROR') { 
				return $event['description'];
	    	}
  		}
  		
  		return '';
	}
}
