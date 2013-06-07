<?php 

/**
 * EYEMAGINE - The leading Magento Solution Partner
 *
 * Merchandising Made Easy
 *
 * @package Eyemagine_Merchandising
 * @author EYEMAGINE <support@eyemaginetech.com>
 * @category Eyemagine
 * @copyright Copyright (c) 2013 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *
 */

require_once 'Mage/Adminhtml/controllers/Catalog/CategoryController.php';

class Eyemagine_Merchandising_Catalog_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{ 
   /**
    * Sort desired positions in the frontend and save
    *
    * @see Mage_Catalog_Model_Category::getProductCollection()
    *
    * @param Mage_Catalog_Model_Category $category
    * @return Varien_Data_Collection_Db
    */
    public function saveAction()
    {
    	if (!$category = $this->_initCategory()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        
        if ($data = $this->getRequest()->getPost()) {
        	
        	$category->addData($data['general']);
            
        	// Retrieve parent ID
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                
                if (!$parentId) {
                    if ($storeId) {
                    	$parentId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    }
                    else {
                        $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
                    }
                }
                
                $parentCategory = Mage::getModel('catalog/category')->load($parentId);
                $category->setPath($parentCategory->getPath());
            }
            
            // Check "Use Default Value" checkboxes values
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());
            
			if (isset($data['category_products']) && !$category->getProductsReadonly()) {
				// get old product associations
				parse_str($data['category_products'], $oldProducts);
            	
            	// Get new sorted list
				parse_str($data['merchandising_products'], $newProducts);

				$totalSortedProducts = count($newProducts);
				
				if (!empty($data['merchandising_products'])) {
					
					$diffProducts = array();
					$i = $totalSortedProducts + 1;
					
                    foreach($oldProducts as $key => $val) {
						if(!array_key_exists($key, $newProducts)){
							$diffProducts[$key] = $i;
							$i++;
						}
					}
					
					$allSortedProducts = $newProducts + $diffProducts;
					
                    $category->setPostedProducts($allSortedProducts);
				} else {
					$category->setPostedProducts($oldProducts);
				}
            }
            
            Mage::dispatchEvent('catalog_category_prepare_save', array(
                'category' => $category,
                'request' => $this->getRequest(),
            ));

			try {
               	$category->save();
				$catgId = $category->getId();

				//$oldProducts = $category->getProductsPosition();				

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('Category saved'));
                $refreshTree = 'true';
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage())
                    ->setCategoryData($data);
                $refreshTree = 'false';
            }
        }
        
        $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $category->getId()));
        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
        );
    }
    
    /**
     * Merchandising Action
     * Display list of products related to sorting of ID, Name, SKU, or Price
     *
     * @return void
     */
   	public function merchandisingAction()
    {
    	if (!$category = $this->_initCategory()) {
    		return;
    	}
    	
    	$this->loadLayout();
    	$this->getResponse()->setBody(
    			$this->getLayout()->createBlock('adminhtml/catalog_category_tab_merchandising')->toHtml()
    	);
    }
}