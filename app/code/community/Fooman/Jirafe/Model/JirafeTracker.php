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
class Fooman_Jirafe_Model_JirafeTracker extends Piwik_PiwikTracker
{

    private $async = false;

    /**
     * the default piwikTracker automatically assigns a visitor id
     * we want to rely on the cookie id only for frontend visitors
     */
    public function __construct($idSite, $apiUrl = false)
    {
        parent::__construct($idSite, $apiUrl);
        if (Mage::getDesign()->getArea() == 'frontend') {
            $this->visitorId = false;
        }
    }
    
    public function setAsyncFlag ($flag)
    {
        $this->async = $flag;
    }
    
    protected function sendRequest ($url)
    {
        return $this->async ? $this->sendRequestAsync($url) : $this->sendRequestSync($url);
    }

    protected function sendRequestSync ($url)
    {
        $client = new Zend_Http_Client($url);
        Mage::helper('foomanjirafe')->debug($url);
        $response = $client->request();

        //check server response
        if ($client->getLastResponse()->isError()) {
            throw new Exception($response->getStatus() . ' ' . $response->getMessage());
        }
        return $response;
    }

    protected function sendRequestAsync ($url)
    {

        $parts = parse_url($url);

        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80,
                $errno, $errstr, 30);

        if (!$fp) {
            return false;
        } else {
            $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
            $out.= "Host: " . $parts['host'] . "\r\n";
            $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out.= "Content-Length: " . strlen($parts['query']) . "\r\n";
            $out.= "Connection: Close\r\n\r\n";
            if (isset($parts['query'])) {
                $out.= $parts['query'];
            }

            fwrite($fp, $out);
            fclose($fp);
            return true;
        }
    }

    public function addEcommerceItem($sku, $name = false, $category = false, $price = false, $quantity = false)
    {
        // Alter price / qty in case an item with the same SKU is already in the cart
        if (!empty($sku) && isset($this->ecommerceItems[$sku])) {
            $old = $this->ecommerceItems[$sku];
            $price = ($old[3] * $old[4] + $price * $quantity) / ($old[4] + $quantity);
            $quantity += $old[4];
        }
        
        parent::addEcommerceItem($sku, $name, $category, $price, $quantity);
    }

}
