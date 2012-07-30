<?php

$installer = $this;

$installer->addAttribute('quote_payment', 'braintree_transaction_id', array());
$installer->addAttribute('order_payment', 'braintree_transaction_id', array());
$installer->addAttribute('invoice', 'braintree_transaction_id', array());
$installer->addAttribute('creditmemo', 'braintree_transaction_id', array());

