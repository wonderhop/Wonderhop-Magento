<?php 

$installer = $this;

$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'email_template', 'int(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'shipping_cost', ' varchar(11) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'attributes_show', 'decimal(12,4) NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'attributes', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'email_header', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'email_message', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'pdf_header', 'text NOT NULL');
$installer->getConnection()->addColumn($this->getTable('supplier_users'), 'pdf_message', 'text NOT NULL');
