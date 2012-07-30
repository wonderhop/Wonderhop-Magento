<?php
 
class Wonderhop_Sales_Block_Adminhtml_Catalog_Category_Tab_Attributes  extends Mage_Adminhtml_Block_Catalog_Form
 
{
    /**
     * Retrieve Category object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }

    /**
     * Initialize tab
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setShowGlobalIcon(true);
    }

    /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes
     */
    protected function _prepareForm() {
        $group      = $this->getGroup();
        $attributes = $this->getAttributes();

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('group_' . $group->getId());
        $form->setDataObject($this->getCategory());

        $fieldset = $form->addFieldset('fieldset_group_' . $group->getId(), array(
            'legend'    => Mage::helper('catalog')->__($group->getAttributeGroupName()),
            'class'     => 'fieldset-wide',
        ));

        if ($this->getAddHiddenFields()) {
            if (!$this->getCategory()->getId()) {
                // path
                if ($this->getRequest()->getParam('parent')) {
                    $fieldset->addField('path', 'hidden', array(
                        'name'  => 'path',
                        'value' => $this->getRequest()->getParam('parent')
                    ));
                }
                else {
                    $fieldset->addField('path', 'hidden', array(
                        'name'  => 'path',
                        'value' => 1
                    ));
                }
            }
            else {
                $fieldset->addField('id', 'hidden', array(
                    'name'  => 'id',
                    'value' => $this->getCategory()->getId()
                ));
                $fieldset->addField('path', 'hidden', array(
                    'name'  => 'path',
                    'value' => $this->getCategory()->getPath()
                ));
            }
        }

        $this->_setFieldset($attributes, $fieldset);

        foreach ($attributes as $attribute) {
        
            /**
             * CUSTOM: Set time format for start date and end date attributes
             */
            if (in_array($attribute->getAttributeCode(), array('start_date','end_date'))) {
                $dateFormatIso = "yyyy-MM-dd HH:mm:ss"; //Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                 error_log($dateFormatIso);
                $element = $form->getElement($attribute->getAttributeCode());
                $element->setTime(true);
               
                $element->setInputFormat( $dateFormatIso);
                $element->setFormat($dateFormatIso);
                 
  
            }
            /* @var $attribute Mage_Eav_Model_Entity_Attribute */
            if ($attribute->getAttributeCode() == 'url_key') {
                if ($this->getCategory()->getLevel() == 1) {
                    $fieldset->removeField('url_key');
                    $fieldset->addField('url_key', 'hidden', array(
                        'name'  => 'url_key',
                        'value' => $this->getCategory()->getUrlKey()
                    ));
                } else {
                    $form->getElement('url_key')->setRenderer(
                        $this->getLayout()->createBlock('adminhtml/catalog_form_renderer_attribute_urlkey')
                    );
                }
            }
        }
        
        /**
          * CUSTOM: remove start date end date for level 1 and 2 
          */
        if (in_array($this->getCategory()->getLevel(), array( 1,2))) {
            $fieldset->removeField('start_date');
            $fieldset->removeField('end_date');
         }   
            
        if ($this->getCategory()->getLevel() == 1) {
            $fieldset->removeField('custom_use_parent_settings');
            
        } else {
            if ($this->getCategory()->getCustomUseParentSettings()) {
                foreach ($this->getCategory()->getDesignAttributes() as $attribute) {
                    if ($element = $form->getElement($attribute->getAttributeCode())) {
                        $element->setDisabled(true);
                    }
                }
            }
            if ($element = $form->getElement('custom_use_parent_settings')) {
                $element->setData('onchange', 'onCustomUseParentChanged(this)');
            }
        }

        if ($this->getCategory()->hasLockedAttributes()) {
            foreach ($this->getCategory()->getLockedAttributes() as $attribute) {
                if ($element = $form->getElement($attribute)) {
                    $element->setReadonly(true, true);
                }
            }
        }

        if (!$this->getCategory()->getId()){
            $this->getCategory()->setIncludeInMenu(1);
        }
         
        $form->addValues($this->getCategory()->getData());

        Mage::dispatchEvent('adminhtml_catalog_category_edit_prepare_form', array('form'=>$form));

        $form->setFieldNameSuffix('general');
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve Additional Element Types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_category_helper_image'),
            'textarea' => Mage::getConfig()->getBlockClassName('adminhtml/catalog_helper_form_wysiwyg')
        );
    }
}
