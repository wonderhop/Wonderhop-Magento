<?php
 
include_once("Mage/Adminhtml/controllers/Catalog/CategoryController.php");
 
class Wonderhop_Sales_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Catalog_CategoryController
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
        $refreshTree = 'false';
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
             * Process "Use Config Settings" checkboxes
             */
            if ($useConfig = $this->getRequest()->getPost('use_config')) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            /**
             * Create Permanent Redirect for old URL key
             */
            if ($category->getId() && isset($data['general']['url_key_create_redirect']))
            // && $category->getOrigData('url_key') != $category->getData('url_key')
            {
                $category->setData('save_rewrites_history', (bool)$data['general']['url_key_create_redirect']);
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products']) &&
                !$category->getProductsReadonly()) {
                $products = array();
                parse_str($data['category_products'], $products);
                $category->setPostedProducts($products);
            }

            Mage::dispatchEvent('catalog_category_prepare_save', array(
                'category' => $category,
                'request' => $this->getRequest()
            ));

            /**
             * Proceed with $_POST['use_config']
             * set into category model for proccessing through validation
             */
            $category->setData("use_post_data_config", $this->getRequest()->getPost('use_config'));

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $category->getResource()->getAttribute($code)->getFrontend()->getLabel()));
                        }
                        else {
                            Mage::throwException($error);
                        }
                    }
                }

                /**
                 * Check "Use Default Value" checkboxes values
                 */
                if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                    foreach ($useDefaults as $attributeCode) {
                        $category->setData($attributeCode, false);
                    }
                }

                /**
                 * Unset $_POST['use_config'] before save
                 */
                $category->unsetData('use_post_data_config');
                
                
                /* Save the right format into database for start and end sale date */
                $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                
                $new_start_date = new Zend_Date($category->getStartDate(), $dateFormatIso);
                $new_end_date   = new Zend_Date($category->getEndDate(), $dateFormatIso);
                
                $category->setStartDate($new_start_date->toString("YYYY-MM-dd HH:mm:ss"));
                $category->setEndDate($new_end_date->toString("YYYY-MM-dd HH:mm:ss"));
                /* END OF Custom Rewrite */
                
                $category->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('catalog')->__('The category has been saved.'));
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

   
}
