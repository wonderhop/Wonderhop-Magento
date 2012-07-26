<?php class Wonderhop_Sales_Helper_Data extends Mage_Core_Helper_Abstract {
	public static function getCountDown($sale){
      
      preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/', $sale->getEndDate(), $match);
      
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
      $countdown = '';
       
      if ($days_left) {
        $countdown .=  $days_left > 1 ? "$days_left days " : "$days_left day ";
      }
      if ($hours_left == 0) {
        $countdown .= $minutes_left." minutes ";
      } else {
        $countdown .= $hours_left." hours ";
      }
      return $countdown;
    }
}
