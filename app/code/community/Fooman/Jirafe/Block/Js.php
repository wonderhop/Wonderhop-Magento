<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Block_Js extends Mage_Core_Block_Template
{
    const VISITOR_ALL       = 'A';
    const VISITOR_BROWSERS  = 'B';
    const VISITOR_ENGAGED   = 'C';
    const VISITOR_READY2BUY = 'D';
    const VISITOR_CUSTOMER  = 'E';
    
    const PAGE_PRODUCT  = 1;
    const PAGE_CATEGORY = 2;
    const PAGE_SEARCH   = 3;
    const PAGE_CART     = 4;
    const PAGE_CONFIRM  = 5;
    
    public $pageLevel = self::VISITOR_BROWSERS;
    public $pageType;
    
    protected $aPageMap = array(
        self::PAGE_PRODUCT => self::VISITOR_ENGAGED,
        self::PAGE_CART    => self::VISITOR_READY2BUY,
        self::PAGE_CONFIRM => self::VISITOR_CUSTOMER,
    );

    /**
     * Set default template
     *
     */
    protected function _construct()
    {
        $this->setTemplate('fooman/jirafe/js.phtml');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function getSiteId()
    {
        return Mage::helper('foomanjirafe')->getStoreConfig('site_id', Mage::app()->getStore()->getId());
    }

    public function getPiwikBaseURL()
    {
        return Mage::getModel('foomanjirafe/jirafe')->getPiwikBaseUrl();
    }

    public function getJsBaseURL()
    {
        return Mage::getModel('foomanjirafe/jirafe')->getJsBaseUrl();
    }
    
    public function getProduct()
    {
        $product = Mage::registry('product');
        if ($product) {
            $aCategories = array();
            foreach ($product->getCategoryIds() as $id) {
                $category = Mage::getModel('catalog/category')->load($id);
                $aCategories[] = $this->getCategory($category);
            }

            $productPrice = $product->getBasePrice();
            // This is inconsistent behaviour from Magento
            // base_price should be item price in base currency
            // TODO: add test so we don't get caught out when this is fixed in a future release
            if (!$productPrice || $productPrice < 0.00001) {
                $productPrice = $product->getPrice();
            }

            return array(
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'categories' => $aCategories,
                'price' => $productPrice,
            );
        } else {
            return array();
        }
    }
    
    public function getCategory($category = null)
    {
        $aCategories = array();
        if (!isset($category)) {
            $category = Mage::registry('current_category');
        }
        if ($category) {
            foreach ($category->getPathIds() as $k => $id) {
                // Skip null and root
                if ($k > 1) {
                    $category = Mage::getModel('catalog/category')->load($id);
                    $aCategories[] = $category->getName();
                }
            }
        }
        return join('/', $aCategories);
    }
    
    public function getJirafePageLevel()
    {
        $level = $this->_getSession()->getJirafePageLevel();
        if (!empty($level) && $level > $this->pageLevel) {
            // Override page type with session data
            $this->pageLevel = $level;
            // Clear session variable
            $this->_getSession()->setJirafePageLevel(null);
        }
        
        return $this->pageLevel;
    }
    
    public function setJirafePageType($type)
    {
        $type = constant(__CLASS__.'::'.$type);
        if (!empty($type)) {
            $this->pageType = $type;
            $this->pageLevel = isset($this->aPageMap[$type]) ? $this->aPageMap[$type] : self::VISITOR_BROWSERS;
        }
    }
    
    public function getTrackingCode()
    {
        $aData = array(
            'id'        => $this->getSiteId(),
            'pageLevel' => $this->getJirafePageLevel(),
        );

        $jsUrl = $this->getJsBaseURL();
        $piwikUrl = $this->getPiwikBaseURL();
        if ($piwikUrl != 'data.jirafe.com') {
            $aData['baseUrl'] = $piwikUrl;
        }

        switch ($this->pageType) {
            case self::PAGE_PRODUCT:
                $aData['product']  = $this->getProduct();
                break;
            case self::PAGE_CATEGORY:
                $aData['category'] = array('name' => $this->getCategory());
                break;
        }
        
        $jirafeJson = json_encode($aData);
    
        return <<<EOF
<!-- Jirafe:START -->
<script type="text/javascript">
var jirafe = {$jirafeJson};
(function(){
    var d=document,g=d.createElement('script'),s=d.getElementsByTagName('script')[0];
    g.type='text/javascript',g.defer=g.async=true;g.src=d.location.protocol+'//{$jsUrl}/jirafe.js';
    s.parentNode.insertBefore(g, s);
})();
</script>
<!-- Jirafe:END -->

EOF;
    }
    
}
