<?php 
/**
 * Custom Category controller
 * customization for storing Tags and related stuff
 *
 * EyeMagine - The leading Magento Solution Partner.
 * 
 * @author     EyeMagine <magento@eyemaginetech.com>
 * @category   Eyemagine
 * @package    Eyemagine_Merchandise
 * @copyright  Copyright (c) 2003-2012 EYEMAGINE Technology, LLC (http://www.eyemaginetech.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html (GPL)
 */
require_once 'Mage/Adminhtml/controllers/Catalog/CategoryController.php';
class Eyemagine_Merchandising_Catalog_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
{ 
    /**
     * Category save
     */
    public function saveAction()
    {
        if (!$category = $this->_initCategory()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        if ($data = $this->getRequest()->getPost()) {
            $category->addData($data['general']);
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
            /**
             * Check "Use Default Value" checkboxes values
             */
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());
            if (isset($data['category_products']) &&
                !$category->getProductsReadonly()) {
				
                // get new product associations
				$newProducts = array();
                parse_str($data['category_products'], $newProducts);

				// get old product associations
				$oldProducts = $category->getProductsPosition();
				
				// merging new and old product associations
				foreach($oldProducts as $key => $val) {
					if(!array_key_exists($key, $newProducts)){
						unset($oldProducts[$key]);
					}
				}
				$products = $oldProducts + $newProducts;

				// handling newly associated products
				foreach($products as $productId => $position) {
					if (empty($position) || $position == 0) {
						$products[$productId] = max($products)+1;
					}
				}
				
				// handling unassocaited products (if any)
				asort($products);
				
				$posCntr = 1;
				foreach($products as $prodId => $pos) {
					$products[$prodId] = $posCntr;
					$posCntr++;
				}
				
				
/*				$positionIndexed = array_flip($products);
				$shifted = array_shift($positionIndexed);
				array_unshift($positionIndexed, 0, $shifted);
				unset($positionIndexed[0]);
				$products = array_flip($positionIndexed);
*/				
				$category->setPostedProducts($products);
            }
			
            Mage::dispatchEvent('catalog_category_prepare_save', array(
                'category' => $category,
                'request' => $this->getRequest()
            ));
			
            try {
                $category->save();
				$catgId = $category->getId();

				$oldProducts = $category->getProductsPosition();				

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
    
	public function merchandisingAction()
    {
        if (!$category = $this->_initCategory()) {
            return;
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_category_tab_merchandising')->toHtml()
        );
    }
	
	public function updateProductPositionsAction()
	{
		$categoryId 	= (int) $this->getRequest()->getParam('id');
		$productId 		= (int) $this->getRequest()->getParam('productId');
		$newPosition	= (int) $this->getRequest()->getParam('newPosition')+1;
		
		// Load Category
		$category = Mage::getModel('catalog/category')->load($categoryId);

		// Get Postions
		$positionsArr = $category->getProductsPosition();
		asort($positionsArr);
		
        // Update product position
		if (!isset($positionsArr[$productId])) {
            $this->_fault('product_not_assigned');
        }
		
		$i = 1;
		foreach($positionsArr as $key => $val) {
			$positions[$key] = $i;
			$i++;
		}
		
		// current product postion
		$currentPosition = $positions[$productId];
		
		// adjusting position values in sequence for all associated products
		$positions = array_flip($positions);
		
		if ($currentPosition < $newPosition) {
			$i = $currentPosition +1;
			// decrement indexes by 1 in pos
			while($i <= $newPosition) {
				$positions[$i-1] = $positions[$i];
				$i++;
			}
		} else if ($currentPosition > $newPosition) {
			$i = $currentPosition - 1;
			// increment indexes by 1 in pos
			while($i >= $newPosition) {
				$positions[$i+1] = $positions[$i];
				$i--;
			}
		}
		$positions[$newPosition] = $productId;
		$positions = array_flip($positions);
		
		
		$positions[$productId] = $newPosition;
		$category->setPostedProducts($positions);

        try {
          $category->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }
		
		// refresh grid
		$this->merchandisingAction();
	}
	
	public function refreshProductTabAction ()
	{
        if (!$category = $this->_initCategory(true)) {
            return;
        }
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_category_tab_product')->toHtml()
        );
	}
}
