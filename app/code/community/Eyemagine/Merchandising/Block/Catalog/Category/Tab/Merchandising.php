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

class Eyemagine_Merchandising_Block_Catalog_Category_Tab_Merchandising extends Mage_Adminhtml_Block_Widget_Grid
{
	protected $_pagerVisibility = false;

    public function __construct()
    {
        parent::__construct();
	        $this->setId('catalog_category_merchandising');
	        $this->setDefaultSort('position');
	        $this->setDefaultDir('ASC');
	        $this->setUseAjax(true);
			$this->setPagerVisibility(false);
			$this->setFilterVisibility(false);
			$this->setDefaultLimit(9999);
					
			if ($this->getRequest()->getParam('id', 0)) {
				$this->setSortableRows(true);
			}
	}

    public function getCategory()
    {
        return Mage::registry('category');
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            }
            elseif(!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(array('in_category'=>1));
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price')
            ->addAttributeToFilter('visibility', array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH))
            ->addStoreFilter($this->getRequest()->getParam('store'))
            ->joinField('position',
                'catalog/category_product',
                'position',
                'product_id=entity_id',
                'category_id='.(int) $this->getRequest()->getParam('id', 0),
                'inner')
			->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
		
		//$collection->getSelect()->order("position asc");
		$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
		$collection->addAttributeToSelect('image');
        $this->setCollection($collection);

		if (!$this->getCategory()->getId()) {
			$this->getCollection()->addFieldToFilter('entity_id', array('in'=>array()));
			$this->setEmptyText("You can use product sorting option after creating category and then doing edit.");
		}

        if ($this->getCategory()->getProductsReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
        }

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		$this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'width'     => '60',
            'index'     => 'entity_id',
            'sortable'  => false
        ));
        $this->addColumn('thumbnail', array(
			'header'=> Mage::helper('catalog')->__('Thumbnail'),
			'width' => '90px',
			'index' => 'image',
			'renderer'  => 'adminhtml/widget_grid_column_renderer_thumbnail',
			'filter' => false,
			'sortable'  => false
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name',
			'sortable'  => false
        ));
        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => '80',
            'index'     => 'sku',
			'sortable'  => false
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('catalog')->__('Price'),
            'type'  => 'currency',
            'width'     => '1',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'     => 'price',
			'sortable'  => false
        ));

		$this->addColumn('inventory', array(
			'header'=> Mage::helper('catalog')->__('Inventory'),
			'width' => '100px',
			'type'  => 'number',
			'index' => 'qty',
			'sortable'  => false
		));

		$this->addColumn('status', array(
			'header'=> Mage::helper('catalog')->__('Status'),
			'width' => '70px',
			'index' => 'status',
			'type'  => 'options',
			'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
			'sortable'  => false
		));

		if ($this->getCategory()->getId()) {
			$this->addColumn('position', array(
				'header'    => Mage::helper('catalog')->__('Position'),
				'width'     => '1',
				'align'     => 'center',
				'type'      => 'number',
				'index'     => 'position',
				'sortable'  => false,
				'renderer'  => 'adminhtml/widget_grid_column_renderer_dragable'
			));
		}

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/merchandising', array('_current'=>true));
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if (is_null($products)) {
            $products = $this->getCategory()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }
}
