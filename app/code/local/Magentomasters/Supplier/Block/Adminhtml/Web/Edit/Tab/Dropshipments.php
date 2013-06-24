<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order Shipments grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Dropshipments
    extends Mage_Adminhtml_Block_Widget_Grid
{

	 public function __construct()
    {
        parent::__construct();
        $this->setId('selectionsGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
		$this->setUseAjax(true);
		$this->setSaveParametersInSession(false); 
    }
    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_shipment_grid_collection';
    }

    protected function _prepareCollection()
    {
        //$collection = Mage::getResourceModel($this->_getCollectionClass())
        //    ->addFieldToSelect('increment_id')
            //->addFieldToSelect('created_at')
            //->addFieldToSelect('increment_id')
           // ->addFieldToSelect('total_qty')
           // ->addFieldToSelect('shipping_name')
           // ->setOrderFilter($this->getOrder())
        //;
		
		//$collection = Mage::getModel('supplier/dropship')->getDropshipments();
		
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection();
		
		$id = $this->getRequest()->getParam('id');
		
		if($id){
			$collection->addFieldToFilter('supplier_id',$id);
		}					
		
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('dropship_id', array(
            'header' => Mage::helper('supplier')->__('Dropship number'),
            'index' => 'dropship_id',
        ));
		
		 $this->addColumn('order_number', array(
            'header' => Mage::helper('supplier')->__('Order number'),
            'index' => 'order_number',
        ));

        $this->addColumn('product_name', array(
            'header' => Mage::helper('supplier')->__('Product Name'),
            'index' => 'product_name',
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('supplier')->__('Total Qty'),
            'index' => 'qty',
            'type'  => 'number',
        ));
		
		 $this->addColumn('date', array(
            'header' => Mage::helper('supplier')->__('Date Dropshipment'),
            'index' => 'date',
            'type' => 'datetime',
        ));
		
		$this->addColumn('method', array(
            'header' => Mage::helper('sales')->__('Output'),
            'index' => 'method',
			'type'      =>'options',
		  	'options'   => 
				
						array(
                                '0'     => Mage::helper('supplier')->__('None'),
								'1'     => Mage::helper('supplier')->__('Email'),
                                '2'     => Mage::helper('supplier')->__('XML'),
								'3'     => Mage::helper('supplier')->__('FTP XML'),
                        ),
        ));
		
		$this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
			'type'      =>'options',
		  	'options'   => 
				
						array(
								'1'     => Mage::helper('supplier')->__('Pending'),
                                '2'     => Mage::helper('supplier')->__('Scheduled'),
                                '3'     => Mage::helper('supplier')->__('Canceled'),
                                '4'     => Mage::helper('supplier')->__('Refunded'),
                                '5'     => Mage::helper('supplier')->__('Completed'), 
                        ),
        ));
		
		$this->addColumn('Order',
                array(
                    'header'    => Mage::helper('supplier')->__('Order'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getOrderId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('supplier')->__('View'),
                            'url'     => array('base'=>'../index.php/admin/sales_order/view/'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
         ));
		
		//$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        
        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
	
	public function getGridUrl()
    {
        //return $this->getUrl('*/*/shipments', array('_current' => true));
		
		return $this->getUrl('supplier/adminhtml_web/dropshipments');
    }
	
	public function getRowUrl($row)
    {
        //return $this->getUrl(
        //    '*/sales_order_shipment/view',
        //    array(
        //        'shipment_id'=> $row->getId(),
        //        'order_id'  => $row->getOrderId()
        //     ));
    }
	
	protected function _prepareMassaction()
   	 {
       //$this->setMassactionIdField('id');
       //$this->getMassactionBlock()->setFormFieldName('dropshipment_ids');
       //$this->getMassactionBlock()->setUseSelectAll(true);

		//$this->getMassactionBlock()->addItem('dropship_credit', array(
		//	'label'=> Mage::helper('supplier')->__("Twinfield Export"),
		//	'url'  => $this->getUrl('*/*/exportDropshipments'),
		//));
		
		//$this->getMassactionBlock()->addItem('dropship_pdf_credit', array(
		//	'label'=> Mage::helper('supplier')->__("PDF Invoice"),
		//	'url'  => $this->getUrl('*/*/exportPdfDropshipments'),
		//));

      	//return $this;
    }
}
