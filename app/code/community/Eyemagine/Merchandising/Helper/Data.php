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