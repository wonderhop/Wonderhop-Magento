<?php 
 
class Wonderhop_Sales_Block_Registered extends Mage_Core_Block_Template {
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
     * Check if customer has just registered
     *      @uses singleton Wonderhop_Generic_Model_Data::isJustRegisteredCustomer
     *      @param $clear (bool) [true] -- wether to clear the session variable that keeps record of this
     *      @return (bool)
     */ 
    public function isRegister($clear = true)
    {
        return Mage::getSingleton('generic/data')->isJustRegisteredCustomer($clear);
    }
    
    /**
     * Check if this is the order success page
     *      @uses Mage::registry -- previously set flag from Wonderhop_Generic_Observer
     *      @return (bool)
     */ 
    public function isOrderPlaced()
    {
        return (bool)Mage::registry('is_order_placed_success');
    }
    
    /**
     * Check is customer is currently logged in
     *      @uses Mage
     *      @return (bool)
     */ 
    public function isLoggedIn()
    {
        return $this->_getCustomerSession()->isLoggedIn();
    }
    
    /**
     * Check if customer has just perfomed the login action
     *      @uses Mage , Mage_Core_Model_Session -- previously set flag on core session by account controller
     *      @return (boolint 1| void)
     */
    public function checkIfCustomerLoggedIn()
    {
        if($this->_getCustomerSession()->isLoggedIn() && Mage::getSingleton('core/session')->getCustomerLoggedIn()) {
            Mage::getSingleton('core/session')->unsCustomerLoggedIn();
            return 1;
        }
    }
    
    /**
     * Check if current page is (string)$check_page by route name
     *      @uses  Mage
     *      @param $check_page (string) -- page route name to compare to
     */
    public function isPage($check_page)
    {
        $page = Mage::app()->getFrontController()->getRequest()->getRouteName();   
        $identifier = Mage::getSingleton('cms/page')->getIdentifier();
        if (!$identifier && $page == $check_page) {
            return 1;
        }
        if ($page == 'cms') {
            return $check_page == $identifier; 
        }
    }
    
    /**
     * Get Mage visitor id from visitor log
     */ 
    public function getVisitorId()
    {
        return Mage::getSingleton ( 'log/visitor' )->getId();
    }
    
    /**
     * Get current customer
     */
    public function getCustomer()
    {
        return $this->_getCustomerSession()->getCustomer();
    }
    
    /**
     * Get curent customer tracking id (customer_id) or empty string if no customer logged in
     */
    public function getCustomerTrackId()
    {
        $session = $this->_getCustomerSession();
        $customer = $session->isLoggedIn() ? $session->getCustomer() : NULL;
        //if ($customer) {
        //    return $customer->getDistinctId() ? $customer->getDistinctId() : $customer->getId();
        //}
        return $customer ? $customer->getId() : '';
    }
    
    /**
     * Get curent customer tracking name(customer email) or <@Visitor> if not customer logged in
     */
    public function getCustomerTrackName()
    {
        if ($this->getCustomerTrackId()) {
            return $this->_getCustomerSession()->getCustomer()->getEmail();
        }
        return '@Visitor';
    }
    
    /**
     * Gets a cookie value by key
     */ 
    public function getCookieVal($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : NULL;
    }
    
    /**
     * Get <Ad> attribute of customer or, $_GET parameter <a> or cookie value <vonderhop_a>
     */ 
    public function getTrackAd()
    {
        $session = $this->_getCustomerSession();
        $customer = $session->isLoggedIn() ? $session->getCustomer() : NULL;
        $asParam = Mage::app()->getFrontController()->getRequest()->getParam('a');
        return $customer ? $customer->getAd() : ($asParam ? $asParam : $this->getCookieVal('wonderhop_a'));
    }
    
    protected $_mkt_keys = array(
        'a' => 'ad', 
        'utm_source' => 'utm_source' ,
        'utm_campaign' => 'utm_campaign' ,
        'utm_medium' => 'utm_medium' ,
        'utm_content' => 'utm_content' ,
    );
    protected $_mkt_prefix = 'wonderhop';
    
    public function getMarketingData($key)
    {
        $session = $this->_getCustomerSession();
        $customer = $session->isLoggedIn() ? $session->getCustomer() : NULL;
        $arg = array_search($key, $this->_mkt_keys);
        $param = $arg ? Mage::app()->getFrontController()->getRequest()->getParam($arg) : NULL;
        return $customer ? $customer->getData($key) : ($param ? $param : $this->getCookieVal("{$this->_mkt_prefix}_{$arg}"));
    }
    
    /**
     *  Get tracking data for current customer|visitor
     */ 
    public function getCustomerTrackData()
    {
        $session = $this->_getCustomerSession();
        $customer = $session->isLoggedIn() ? $session->getCustomer() : NULL;
        if ($customer) {
            $data = array(
                '$email'        => $customer->getEmail(),
                '$created'      => $customer->getCreatedAt(),
                '$first_name'   => $customer->getFirstname(),
                '$last_name'    => ((trim($customer->getLastname()) == '-') ? '' : $customer->getLastname()) , 
                'id'            => $customer->getId(),
                'ad_code'       => $customer->getAd(),
                'utm_source'    => $customer->getUtmSource(),
                'utm_campaign'  => $customer->getUtmCampaign(),
                'utm_medium'    => $customer->getUtmMedium(),
                'utm_content'   => $customer->getUtmContent(),
            );
        } else {
            $data = array(
                'visitor_id'    => $this->getVisitorId(),
                '$first_name'   => $this->getCustomerTrackName(),
                'id'            => 'v_'.$this->getVisitorId(),
                'ad_code'       => $this->getMarketingData('ad'),
                'utm_source'    => $this->getMarketingData('utm_source'),
                'utm_campaign'  => $this->getMarketingData('utm_campaign'),
                'utm_medium'    => $this->getMarketingData('utm_medium'),
                'utm_content'   => $this->getMarketingData('utm_content'),
            );
        }
        return $data;
    }
    
    
    /* * 
     * Queue events
     *      @param $name(string| bool(true) | NULL)    -- the event name or (bool)true to get the queue , or NULL to reset the queue
     *      @param $data(array)     -- event data
     *      @return $this -- setter/resettter | $queue -- getter ( $name (bool)true)
     */ 
    public function trackEventsQueue($name = true, $data = array())
    {
        static $queue = array();
        if ($name === true) return $queue;
        
        if ($name === NULL)
        {
            $queue = array();
            return $this;
        }
        
        if ( ! (is_string($name) and $name)) return $this;
        
        if ($data === false)
        {
            unset($queue[$name]);
        }
        elseif (isset($queue[$name]))
        {
            $queue[$name] = array_merge($queue[$name], $data);
        }
        else
        {
            $queue[$name] = is_array($data) ? $data : array();
        }
        
        return $this;
    }
    
    /** 
     * Load events before render
     */
    protected function _beforeToHtml()
    {
        return $this->loadEvents();
    }
    
    /**
     * Load events (once) in the track events queue
     */
    public function loadEvents()
    {
        static $loaded = false;
        if ($loaded) return $this;
        
        $data = $this->getCustomerTrackData();
        $pageData = $data;
        $pageEvent = '';
        if($this->isPage('home')) {
            $pageEvent = 'Home page visited';
        } elseif($this->isPage('shops')) {
            $pageEvent = 'Shops page visited';
        } elseif($this->isPage('gift-explorer')) {
            $pageEvent = 'Gift Explorer visited'; 
        } elseif(Mage::registry('current_product')) {
            $pageEvent = 'Product page visited'; 
            $pageData['product'] = Mage::registry('current_product')->getName();
            if (Mage::registry('is_collection') and Mage::registry('current_category'))
            {
                $pageData['in_collection'] = Mage::registry('current_category')->getName();
            }
        } elseif(Mage::registry('current_category')) {
            $collection = 'Collection page visited';
            $sale = 'Sale page visited';
            $category = Mage::registry('current_category')->getName();
            if (Mage::registry('is_collection'))
            {
                $pageEvent = $collection; 
                $pageData['collection'] = $category;
                $remove = $sale;
                $pageData['ad_code'] = isset($data['ad_code']) ? $data['ad_code'] : NULL;
            }
            else
            {
                $pageEvent = $sale; 
                $pageData['sale'] = $category;
                $remove = $collection;
            }
        } elseif($this->isOrderPlaced()) {
            $pageEvent = 'Order placed';
        }
        if ($pageEvent) {
            $this->trackEventsQueue($pageEvent, $pageData);
        }
        if ($this->isRegister()) {
            $this->trackEventsQueue('User registered', $data);
        }
        if ($this->checkIfCustomerLoggedIn()) {
            $this->trackEventsQueue('Logged in', $data);
        }
        
        if (isset($remove))
        {
            $this->trackEventsQueue($remove, false);
        }
        
        //$loaded = true;
        
        return $this;
    }
    
    /** 
     * Check if $event exists in the queue
     */ 
    public function trackEventExists($event)
    {
        $queue = $this->trackEventsQueue();
        return isset($queue[$name]);
    }
    
} 
