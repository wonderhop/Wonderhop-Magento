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
    
    
    public function captureCallbackOutput($callback, $args = array(), $returnType = NULL)
    {
        $returnType = ($returnType === NULL) ? 'output' : ($returnType ? 'result' : 'both');
        ob_start();
        $result = call_user_func_array($callback, $args);
        $output = ob_get_clean();
        $both = array($output, $result, 'output' => $output, 'result' => $result);
        return $$returnType;
    }
    
    public function getGifShopFilterData($catBlock, $rangeBlockId = 'gs_price_ranges')
    {
        $rangesStr = (string)$catBlock->getLayout()->createBlock('cms/block')->setBlockId($rangeBlockId)->toHtml();
        $ranges = array();
        if ($rangesStr) {
            $_ranges = explode('*',strip_tags($rangesStr));
            foreach($_ranges as $_range) {
                $_dec = trim(html_entity_decode(trim($_range)));
                if ( ! $_dec) continue;
                $_data = strtolower($_dec);
                $_limits = substr_count($_data,'$');
                if ($_limits < 1 or $_limits > 2) continue;
                $_parsed = '';
                if ($_limits == 1) {
                    $_parsed = self::_parseSinglePriceLimit($_data);
                } elseif ($_limits == 2) {
                    $_parsed = self::_parseDoublePriceLimits($_dec);
                }
                if ( ! $_parsed) continue;
                $class = 'price-'.$_parsed;
                if (isset($ranges[$class])) continue;
                $ranges[$class] = $_range;
            }
        }
        return array(
            'tags' => $catBlock->getActiveTagClasses(),
            'labels' => $catBlock->getActiveTagNames(), 
            'prices' => $catBlock->getAllProductPrices(),
            'ranges' => $ranges,
        );
    }
    
    
    public static function _parseSinglePriceLimit($line)
    {
        $line = trim(preg_replace('/\s+/',' ',strtolower(str_replace('$','', $line))));
        foreach(array(
            'lteq'  => '/(\<\s*\=|\-|less\s+than\s+((or\s)?equal|(\s+\d+[^i]+)including))/i',
            'gteq'  => '/(\=\s*\>|\+|more\s+than\s+((or\s)?equal|(\s+\d+[^i]+)including))/i',
            'lt'  => '/(\<|less\s+than)/i',
            'gt'  => '/(\>|more\s+than)/i',
        ) as $_op => $re) {
            if (preg_match($re, $line) and strlen($num = preg_replace('/[^\d\.]/','',$line)))
                return "single-{$_op}-".intval($num);
        }
        return '';
    }
    
    public static function _parseDoublePriceLimits($line)
    {
        $line = explode('-', $line, 2);
        $limit_a = intval(preg_replace('/[^\d\.]/','',$line[0]));
        $limit_b = intval(preg_replace('/[^\d\.]/','',$line[1]));
        return "double-gteq-{$limit_a}-lteq-{$limit_b}";
    }
    
}
