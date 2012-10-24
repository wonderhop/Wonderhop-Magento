<?php class Wonderhop_Sales_Helper_Data extends Mage_Core_Helper_Abstract {
    public static function getCountDown($sale){
    
      $now = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
      $compare_date = $sale->getEndDate();
      $is_start = 0;
      if ($now < $sale->getStartDate()) {
        $is_start = 1;
        $compare_date = $sale->getStartDate();
      }

      preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/', $compare_date, $match);
      
      $match = array_slice($match, 1);
      
      list($year, $month, $day, $hour, $minute) = $match;
      
      // make a unix timestamp for the given date
      $the_countdown_date = mktime($hour, $minute, 0, $month, $day, $year, -1);
      
      // get current unix timestamp
      $today = Mage::getModel('core/date')->timestamp(time());
        
       
      $difference = $the_countdown_date - $today;
      if ($difference < 0) $difference = 0;
      $days_left = floor($difference/60/60/24);
      $hours_left = floor(($difference - $days_left*60*60*24)/60/60);
      $minutes_left = floor(($difference - $days_left*60*60*24 - $hours_left*60*60)/60);
      
      // OUTPUT
      
      $countdown = 'Shop closes in ';
      
      if ($is_start) {
        $countdown = 'Shop opens in ';  
      } 
      if ($days_left) {
        $countdown .=  $days_left > 1 ? "$days_left days " : "$days_left day ";
      }
      if ($hours_left == 0) {
        $countdown .= $minutes_left." minute";
        if ($minutes_left > 1) $countdown .= 's';
      } else {
        $countdown .= $hours_left." hour";
        if ($hours_left > 1) $countdown .= 's';
      }
      if (!$is_start && $hours_left == 0 && $minutes_left == 0) {
        $countdown = 'Shop closed';
      }
      return $countdown;
    }
    
    public function getShortCountDown($sale)
    {
        $text = self::getCountDown($sale);
        if(preg_match('/days?\s+[\d]{1,2}\s+hours/i',$text))
            $text = preg_replace('/\s+[\d]{1,2}\s+hours/i','',$text);
        return $text;
    }
}
