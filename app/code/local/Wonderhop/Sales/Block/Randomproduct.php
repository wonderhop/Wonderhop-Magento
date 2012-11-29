<?php

class Wonderhop_Sales_Block_Randomproduct extends Mage_Core_Block_Template {
    
    
    protected $_product, $_category, $_tracelimit = 100;
    
    protected function _getProductAndCategoriesIds()
    {
        $salesBlock = $this->getLayout()
            ->createBlock('wonderhop_sales/sales', 'wh_sales_block');
        $sections = $salesBlock->getSaleSections();
        $products = array();
        $categories = array();
        foreach($sections as $name => $section) {
            $sales = $salesBlock->getSales($section['interval']);
            if($sales and count($sales)) {
                foreach($sales as $sale) {
                    $_cat = Mage::getModel('catalog/category')->load($sale->getId());
                    $_prods = Mage::getModel('catalog/product')->getCollection()
                        ->addCategoryFilter( $_cat )->load()->getAllIds();
                    $products = array_unique(array_merge($products, $_prods));
                    $categories= array_unique(array_merge($categories, array($_cat->getId())));
                }
            }
        }
        return array($products, $categories);
    }
    
    protected function _getCategory($product)
    {
        $gs_cat_id = (int)(string)Mage::getConfig()->getNode('localconf/modules/catalog/category/giftshop_cat_id');
        $gs = false;
        $categoryIds = $product->getCategoryIds();
        $category = NULL;
        foreach($categoryIds as $catId) {
            $_cat = Mage::getSingleton('catalog/category')->load($catId);
            if ($_cat->getName() == 'Sales') {
                continue;
            } elseif ($_cat->getId() == $gs_cat_id) {
                $gs = $_cat;
                continue;
            } else {
                $category = $_cat;
                break;
            }
        }
        $no_valid_category = (($category and ! in_array($category->getId(), $categories)) or ! $category);
        if ($no_valid_category and $gs) $category = $gs;
        if ( ! $category) {
            error_log('NO category found for product -- "'. $product->getName() . '" , id: ' . $product->getId());
            return NULL;
        }
        return $category;
    }
    
    protected function _getRandomTrace()
    {
        $trace = array();
        $session = Mage::getSingleton('customer/session');
        if ($session->getRandomProductTrace()) {
            $trace = explode(',', $session->getRandomProductTrace());
        }
        return $trace;
    }
    
    protected function _setRandomTrace($trace)
    {
        $trace = (array)$trace;
        $session = Mage::getSingleton('customer/session');
        if (count($trace) > $this->_tracelimit) {
            $trace = array_slice($trace, count($trace) - $this->_tracelimit);
        }
        $session->setRandomProductTrace(implode(',', $trace));
    }
    
    protected function _traceProduct($product)
    {
        $this->_setRandomTrace( array_merge( $this->_getRandomTrace(), array($product->getId()) ) ); 
    }
    
    public function getRandomProductWithCategory($trace = true)
    {
        $randomTrace = $this->_getRandomTrace();
        list($products, $categories) = $this->_getProductAndCategoriesIds();
        $products = $this->_selectSaleableProductIds($products);
        $random = mt_rand(0, count($products) -1);
        $lastRandomProductId = end( $randomTrace );
        if (count($products) > 1 and $lastRandomProductId and $lastRandomProductId == $products[$random]) {
            while($lastRandomProductId == $products[$random]) {
                $random = mt_rand(0, count($products) -1);
            }
        }
        $product = Mage::getSingleton('catalog/product')->load($products[$random]);
        if ($trace) $this->_traceProduct($product);
        $category = $this->_getCategory($product);
        return array($product, $category);
    }
    
    protected function _selectSaleableProductIds($productIds)
    {
        $selected = array();
        foreach($productIds as $id) {
            if ( ! Mage::getModel('catalog/product')->load($id)->isSaleable()) continue;
            $selected[] = $id;
        }
        return $selected;
    }
    
    public function getRandomProduct()
    {
        list($product, $category) = $this->getRandomProductWithCategory();
        return $product;
    }
    
    
    public function reload($register = true)
    {
        list($this->_product, $this->_category) = $this->getRandomProductWithCategory();
        if ($register and ! Mage::registry('current_product')) {
            Mage::register('current_product', $product);
        }
        if ($register and $category and ! Mage::registry('current_category')) {
            Mage::register('current_category', $category);
        }
    }
    
    public function getProduct($register = true)
    {
        if ( ! $this->_product) $this->reload($register);
        return $this->_product;
    }
    
    public function getCategory($register = true)
    {
        if ( ! $this->_product) $this->reload($register);
        return $this->_category;
    }
    
}
