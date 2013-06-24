<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('webGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('supplier/supplier')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
                'header'    => Mage::helper('supplier')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'id',
        ));

        $this->addColumn('name', array(
                'header'    => Mage::helper('supplier')->__('Supplier Name'),
                'align'     =>'left',
                'index'     => 'name',
        ));

        $this->addColumn('surname', array(
                'header'    => Mage::helper('supplier')->__('Name'),
                'align'     => 'left',
                'index'     => 'surname',
        ));
        $this->addColumn('email1', array(
                'header'    => Mage::helper('supplier')->__('First E-Mail'),
                'align'     => 'left',
                'index'     => 'email1',
        ));
        
        $this->addColumn('email2', array(
                'header'    => Mage::helper('supplier')->__('Second E-Mail'),
                'align'     => 'left',
                'index'     => 'email2',
        ));
        
        $this->addColumn('email2', array(
                'header'    => Mage::helper('supplier')->__('Second E-Mail'),
                'align'     => 'left',
                'index'     => 'email2',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()->addItem('delete', array(
                'label'    => Mage::helper('supplier')->__('Delete'),
                'url'      => $this->getUrl('*/*/delete'),
                'confirm'  => Mage::helper('supplier')->__('Are you sure?')
        ));
        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}