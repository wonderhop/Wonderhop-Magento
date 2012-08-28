<?php
/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    OSL
 */
 
class J2t_Ajaxcheckout_Block_Productdetails extends Mage_Catalog_Block_Product_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('j2tajaxcheckout/product_details.phtml');
    }
    
    /*public function getProductDetailsHtml($_product)
    {
        $this->setTemplate('j2tajaxcheckout/product_details.phtml');
        $this->setProduct($_product);
        return $this->toHtml();
    }*/
    
    public function getProductImageWidth(){
        $photo_arr = explode("x",Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_image_size', Mage::app()->getStore()->getId()));
        return $photo_arr[0];
    }
    
    public function getProductImageHeight(){
        $photo_arr = explode("x",Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_image_size', Mage::app()->getStore()->getId()));
        if(isset($photo_arr[1])){
            return $photo_arr[1];
        }
        $photo_arr[0];
    }
}