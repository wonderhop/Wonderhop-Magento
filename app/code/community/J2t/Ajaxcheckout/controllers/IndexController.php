<?php

/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    OSL
 */

class J2t_Ajaxcheckout_IndexController extends /*Mage_Checkout_CartController*/ Mage_Core_Controller_Front_Action
{

    const CONFIGURABLE_PRODUCT_IMAGE= 'checkout/cart/configurable_product_image';
    const USE_PARENT_IMAGE          = 'parent';

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function productdetailsAction()
    {
        $id = $this->getRequest()->getParam('product');
        if (!is_numeric($id)){
            $base_url = Mage::getBaseUrl();
            $full_url = $this->getRequest()->getParam('full_url');
            $path = str_replace($base_url, '', $full_url);
            $store_id = $this->getRequest()->getParam('current_store_id');
            $url_rewrite = Mage::getModel('core/url_rewrite')->setStoreId($store_id)->loadByRequestPath($path);
            if ($product_id = $url_rewrite->getProductId()){
                $_product = Mage::getModel('catalog/product')->load($product_id);
            } else {
                $id = str_replace('j2t-url-product-','', $id);
                $_product = Mage::getModel('catalog/product')->loadByAttribute('url_key', $id);
            }
        } else {
            $_product = Mage::getModel('catalog/product')->load($id);
        }
        if ($_product->getId()){
            echo $this->getLayout()->createBlock('j2tajaxcheckout/productdetails')
                    ->setProduct($_product)
                    ->toHtml();
        } else {
            echo $this->__('Cannot find item');
        }
        exit;
    }

    public function cartdeleteAction()
    {
        if ($this->getRequest()->getParam('btn_lnk')){
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                try {
                    Mage::getSingleton('checkout/cart')->removeItem($id)
                      ->save();
                } catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
                }
            }
            $this->loadLayout();
            $this->_initLayoutMessages('checkout/session');

            $this->renderLayout(); 
        } else {
            $backUrl = $this->_getRefererUrl();
            $this->getResponse()->setRedirect($backUrl);
        }
        
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    
    protected function _massConfAdd($cart, $params)
    {
        $productId = (int) $this->getRequest()->getParam('product');
        

        try {
            if (isset($params['qty_super_attribute'])){
                $hasbeenprocessed = false;
                foreach($params['qty_super_attribute'] as $key_att_mixed => $qty_att){
                    if ($qty_att > 0){
                        $key_att_mixed_array = explode('|', $key_att_mixed);
                        foreach($key_att_mixed_array as $key_att){
                            $hasbeenprocessed = true;
                            $sup_att_array = explode('_',$key_att);
                            if ($params['super_attribute'] == array()){
                                $params['super_attribute'] = array($sup_att_array[0] => $sup_att_array[1]);
                            } else {
                                $params['super_attribute'][$sup_att_array[0]] = $sup_att_array[1];
                            }
                        }

                        $filter = new Zend_Filter_LocalizedToNormalized(
                            array('locale' => Mage::app()->getLocale()->getLocaleCode())
                        );
                        $params['qty'] = $filter->filter($qty_att);
                        $related = $this->getRequest()->getParam('related_product');
                        $params_processed = $params;
                        unset($params_processed['qty_super_attribute']);
                        
                        if ($productId) {
                            $product = Mage::getModel('catalog/product')
                                ->setStoreId(Mage::app()->getStore()->getId())
                                ->load($productId);

                        }


                        $cart->addProduct($product, $params_processed);
                        if (!empty($related)) {
                            $cart->addProductsByIds(explode(',', $related));
                        }
                    }
                }
                if (!$hasbeenprocessed){
                    $message = $this->__('Please specify product quantity.');
                    $this->_getSession()->addError($message);
                    return;
                }
                $cart->save();
            } else {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $params['qty'] = $filter->filter($params['qty']);
                }

                $product = $this->_initProduct();
                $related = $this->getRequest()->getParam('related_product');

                /**
                 * Check product availability
                 */
                if (!$product) {
                    $this->_goBack();
                    return;
                }

                $cart->addProduct($product, $params);
                if (!empty($related)) {
                    $cart->addProductsByIds(explode(',', $related));
                }

                $cart->save();
            }

            $this->_getSession()->setCartWasUpdated(true);

            
            $img = '';
            Mage::dispatchEvent('checkout_cart_add_product_complete', array('product'=>$product, 'request'=>$this->getRequest()));

            $photo_arr = explode("x",Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_image_size', Mage::app()->getStore()->getId()));

            $prod_img = $product;
            if($product->isConfigurable() && isset($params['super_attribute'])){
                $attribute = $params['super_attribute'];
                if (Mage::getStoreConfig(self::CONFIGURABLE_PRODUCT_IMAGE) != self::USE_PARENT_IMAGE) {
                    $prod_img_temp = Mage::getModel("catalog/product_type_configurable")->getProductByAttributes($attribute, $product);
                    if ($prod_img_temp->getImage() != 'no_selection' && $prod_img_temp->getImage()){
                        $prod_img = $prod_img_temp;
                    }
                }
            }
            $img = '<img src="'.Mage::helper('catalog/image')->init($prod_img, 'thumbnail')->resize($photo_arr[0],$photo_arr[1]).'" width="'.$photo_arr[0].'" height="'.$photo_arr[1].'" />';
            $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());

            Mage::getSingleton('checkout/session')->addSuccess('<div class="j2tajax-checkout-img">'.$img.'</div><div class="j2tajax-checkout-txt">'.$message.'</div>');
        }
        catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
        }
    }
    

    public function cartAction()
    {
        if ($this->getRequest()->getParam('cart')){
            if ($this->getRequest()->getParam('cart') == "delete"){
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    try {
                        Mage::getSingleton('checkout/cart')->removeItem($id)
                          ->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
                    }
                }
            }
        }

        if ($this->getRequest()->getParam('product')) {
            $cart   = Mage::getSingleton('checkout/cart');
            $params = $this->getRequest()->getParams();
            $related = $this->getRequest()->getParam('related_product');
            
            //J2T check massconf update
            if(Mage::getConfig()->getModuleConfig('J2t_Massconf')->is('active', 'true') && isset($params['qty_super_attribute'])){
                $this->_massConfAdd($cart, $params);
                $this->loadLayout();
                $this->_initLayoutMessages('checkout/session');
                $this->renderLayout();
                return;
            }
            //J2T check massconf update

            $productId = (int) $this->getRequest()->getParam('product');
            if ($productId) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                try {

                    if (!isset($params['qty'])) {
                        $params['qty'] = 1;
                    }
                    
                    $is_update = false;
                    if (($this->getRequest()->getParam('cart') == 'updateItemOptions') && ($id = $this->getRequest()->getParam('id'))){
                        $quoteItem = $cart->getQuote()->getItemById($id);
                        if (!$quoteItem) {
                            Mage::throwException($this->__('Quote item is not found.'));
                        }
                        
                        $item = $cart->updateItem($id, new Varien_Object($params));
                        if (is_string($item)) {
                            Mage::throwException($item);
                        }
                        if ($item->getHasError()) {
                            Mage::throwException($item->getMessage());
                        }

                        if (!empty($related)) {
                            $cart->addProductsByIds(explode(',', $related));
                        }
                        $is_update = true;
                        
                    } else {
                        $cart->addProduct($product, $params);
                        if (!empty($related)) {
                            $cart->addProductsByIds(explode(',', $related));
                        }
                    }

                    /*$cart->addProduct($product, $params);
                    if (!empty($related)) {
                        $cart->addProductsByIds(explode(',', $related));
                    }*/
                    $cart->save();
                    $this->_getSession()->setCartWasUpdated(true);


                    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                    Mage::getSingleton('checkout/session')->setCartInsertedItem($product->getId());

                    $img = '';
                    //Mage::dispatchEvent('checkout_cart_add_product_complete', array('product'=>$product, 'request'=>$this->getRequest()));
                    
                    
                    if ($is_update){
                        Mage::dispatchEvent('checkout_cart_update_item_complete',
                            array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
                        );
                    } else {
                        Mage::dispatchEvent('checkout_cart_add_product_complete', array('product'=>$product, 'request'=>$this->getRequest()));
                    }
                    
                    $photo_arr = explode("x",Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_image_size', Mage::app()->getStore()->getId()));

                    $prod_img = $product;
                    if($product->isConfigurable() && isset($params['super_attribute'])){
                        $attribute = $params['super_attribute'];
                        if (Mage::getStoreConfig(self::CONFIGURABLE_PRODUCT_IMAGE) != self::USE_PARENT_IMAGE) {
                            $prod_img_temp = Mage::getModel("catalog/product_type_configurable")->getProductByAttributes($attribute, $product);
                            if ($prod_img_temp->getImage() != 'no_selection' && $prod_img_temp->getImage()){
                                $prod_img = $prod_img_temp;
                            }
                        }
                    }
                    $img = '<img src="'.Mage::helper('catalog/image')->init($prod_img, 'thumbnail')->resize($photo_arr[0],$photo_arr[1]).'" width="'.$photo_arr[0].'" height="'.$photo_arr[1].'" />';
                    //$message = $this->__('%s was successfully added to your shopping cart.', $product->getName());
                    
                    if ($is_update){
                        $message = $this->__('%s was updated in your shopping cart.', $product->getName());
                    } else {
                        $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());
                    }
                    
                    Mage::getSingleton('checkout/session')->addSuccess('<div class="j2tajax-checkout-img">'.$img.'</div><div class="j2tajax-checkout-txt">'.$message.'</div>');
                }
                catch (Mage_Core_Exception $e) {
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            Mage::getSingleton('checkout/session')->addError($message);
                        }
                    }
                }
                catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException($e, $this->__('Can not add item to shopping cart'));
                }

            }
        }
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');

        $this->renderLayout();
    }


    public function productoptionAction()
    {
        //getProductUrlSuffix
        echo 'test';
        die;
    }
    
    public function productcheckAction()
    {
        $params = $this->getRequest()->getParams();
        
        $productTypes = array(
            Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
        );
        
        $storeId = Mage::app()->getStore()->getId();
        
        if($product_id = $params['product']){
            
            $product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);
            if ($product->getId()){
                if($product->getHasOptions() || in_array($product->getTypeId(), $productTypes)){
                    //echo get product url
                    echo $product->getProductUrl();
                    die;
                }
            }
        }
        echo 0;
        die;
    }

    public function addtocartAction()
    {
        $this->indexAction();
    }



    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
    }


}