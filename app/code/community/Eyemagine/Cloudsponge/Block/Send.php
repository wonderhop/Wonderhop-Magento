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

class Eyemagine_Cloudsponge_Block_Send extends Mage_Sendfriend_Block_Send
{
	/**
	 * Is the CloudSponge module enabled?
	 * 
	 * @return int
	 */
	public function isEnabled()
	{
		return Mage::helper('cloudsponge')->isEnabled();
	}
}
