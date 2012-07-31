<?php 
 
class Wonderhop_Sales_Block_Sales extends Mage_Core_Block_Template {
	 
    /**
     * Retrieve Customer Session instance
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    
    public function getCalendarSales() {
         $categories = Mage::getModel('catalog/category')
					->getCollection()
					->addAttributeToSelect('*')
					->addIsActiveFilter()
					->addFieldToFilter('level', 3)
					->addOrderField('start_date');
	     $result = array();
	    
	     $locale = Mage::app()->getLocale()->getLocale();
         $long_format =  'yyyy-MM-dd HH:mm:ss';    
	     
	     foreach($categories as $sale) {
            $iso_date = new Zend_Date($sale->getStartDate(), $long_format, $locale);
	        $date = sprintf("%s - %s %s", $iso_date->get(Zend_Date::WEEKDAY), $iso_date->get(Zend_Date::MONTH_NAME), $iso_date->get(Zend_Date::DAY));
 
	        $result[$date][] = $sale;
	     }
	     
	     return $result;
    }
    
    /**
     * Retrieve the sales/today sales
     *
     */
    
    public function getSales($interval) {
        
        $from = NULL;
        if (isset($interval['from'])) {
            $from = $interval['from'];
        }
        
        $to   = NULL;
        if (isset($interval['to'])) {
            $to = $interval['to'];
        }       
        
        $categories = Mage::getModel('catalog/category')
					->getCollection()
					->addAttributeToSelect('*')
					->addIsActiveFilter()
					->addFieldToFilter('level', 3)
					->addOrderField('start_date');
   	    if ($from) {
	        $categories->addAttributeToFilter('start_date', array($interval['from_op'] => $from));
    	    if (isset($interval['today'])) {
                $categories->addAttributeToFilter('start_date', array('lteq' => "$from 23:59:59"));
                $categories->addAttributeToFilter('end_date', array('gt' => "$from 23:59:59"));
             }       
        }
					 
	    
	    if ($to) {
	        $categories->addAttributeToFilter('end_date', array(array($interval['to_op'] => $to)));
	        if (isset($interval['end'])) {
	            $categories->addAttributeToFilter('end_date', array('gteq' => date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()))));
	        }
 
	    }
	    
	    return $categories;
    }
    
    /**
     *  Retrives the sale sections headings and category intervals
     */
    
    public function getSaleSections() {
        $date = new DateTime(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
        date_add($date, date_interval_create_from_date_string('24 hours'));
        return array('Shops Opening Today'          => array('from' => date("Y-m-d", Mage::getModel('core/date')->timestamp(time())), 'from_op' => 'gteq', 'to' => date("Y-m-d 23:59:59", Mage::getModel('core/date')->timestamp(time())), 'to_op' => 'gt', 'today' => '1' ), 
                     'Currently opened shops'        => array('from' => date("Y-m-d", Mage::getModel('core/date')->timestamp(time())), 'from_op' => 'lt', 'to' => $date->format("Y-m-d H:i:s"), 'to_op' => 'gt'), 
                     'Shops Bidding Adieu' => array('to' => $date->format("Y-m-d H:i:s"), 'to_op' => 'lteq', 'end' => 1));
    }

    /**
     * Returns the resized category image
     */
    public function getResizedImage($sale, $width, $height = null, $quality = 100, $thumbnail = True) {
        
        $image = $sale->getThumbnail();
        if (!$thumbnail) {
            $image = $sale->getImage();
        }
        
        if (! $image )
            return false;
        
        $imageUrl = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "category" . DS . $image;
        if (! is_file ( $imageUrl ))
            return false;
        
        $imageResized = Mage::getBaseDir ( 'media' ) . DS . "catalog" . DS . "product" . DS . "cache" . DS . "cat_resized" . DS . $width . '_' . $height . '_' . $image;
       
        // Because clean Image cache function works in this folder only
        if (! file_exists ( $imageResized ) && file_exists ( $imageUrl ) || file_exists($imageUrl) && filemtime($imageUrl) > filemtime($imageResized)) :
            $imageObj = new Varien_Image ( $imageUrl );
            $imageObj->constrainOnly ( true );
            $imageObj->keepAspectRatio ( true );
            $imageObj->keepFrame ( false );
            $imageObj->quality ( $quality );
            $imageObj->resize ( $width, $height );
            $imageObj->save ( $imageResized );
            
        endif;
        
        if(file_exists($imageResized)){
            return Mage::getBaseUrl ( 'media' ) ."/catalog/product/cache/cat_resized/" .$width . '_' . $height . '_' . $image;
        }else{
            return $imageUrl;
        }
    
    }
    
    
    
 
	 
}
