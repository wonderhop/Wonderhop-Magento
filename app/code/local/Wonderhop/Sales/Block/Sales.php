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
	    }
					 
	    
	    if ($to) {
	        $categories->addAttributeToFilter('end_date', array($interval['to_op'] => $to));
	    }
	    
	    return $categories;
    }
    
    /**
     *  Retrives the sale sections headings and category intervals
     */
     
    public function getSaleSections() {
        
        
        
        return array('New Sales'          => array('from' => date("y-m-d"), 'from_op' => 'gteq', 'to' => date("y-m-d 23:59:59"), 'to_op' => 'gt' ), 
                     'Other Sales'        => array('from' => date("y-m-d"), 'from_op' => 'lt', 'to' => date("y-m-d 23:59:59"), 'to_op' => 'gt'), 
                     'Sales About To End' => array('to' => date("y-m-d 23:59:59"), 'to_op' => 'lteq'));
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
    
    
    function getCountDown($sale){
      
      preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/', $sale->getEndDate(), $match);
      
      $match = array_slice($match, 1);
      
      list($year, $month, $day, $hour, $minute) = $match;
      
      // make a unix timestamp for the given date
      $the_countdown_date = mktime($hour, $minute, 0, $month, $day, $year, -1);
      // get current unix timestamp
      $today = time();
      $difference = $the_countdown_date - $today;
      if ($difference < 0) $difference = 0;
      $days_left = floor($difference/60/60/24);
      $hours_left = floor(($difference - $days_left*60*60*24)/60/60);
      $minutes_left = floor(($difference - $days_left*60*60*24 - $hours_left*60*60)/60);
      
      // OUTPUT
      $countdown = '';
      if ($days_left) {
        $countdown .= "$days_left days ";
      }
      
      $countdown .= $hours_left." hours ".$minutes_left." minutes";
      return $countdown;
    }
 
	 
}
