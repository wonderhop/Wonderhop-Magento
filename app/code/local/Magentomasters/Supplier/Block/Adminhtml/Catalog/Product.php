<?php class Magentomasters_Supplier_Block_Adminhtml_Catalog_Product extends Mage_Adminhtml_Block_Catalog_Product{

	protected function _prepareLayout()
    {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('catalog')->__('Add Product'),
            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
            'class'   => 'add'
        ));

        $this->setChild('supplier.product.grid', $this->getLayout()->createBlock('supplier/adminhtml_catalog_product_grid', 'supplier.product.grid'));
        return parent::_prepareLayout(); 
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('supplier.product.grid');
    }

}
