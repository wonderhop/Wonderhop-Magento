<?php
/**
 * Date Helper
 *
 * EyeMagine - The leading Magento Solution Partner.
 * 
 * @author     EyeMagine <magento@eyemaginetech.com>
 * @category   Eyemagine
 * @package    Eyemagine_Merchandise
 * @copyright  Copyright (c) 2003-2012 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html (GPL)
 */

class Eyemagine_Merchandising_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getImageUrl($imageFile)
    {
        return Mage::getBaseUrl('media') . "catalog/product{$imageFile}";
    }


    public function getFileExists($imageFile)
    {
        return file_exists("media/catalog/product{$imageFile}");
    }
}
