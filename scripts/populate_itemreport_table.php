<?php

define('SCRIPTCODE', basename(__FILE__));
require_once 'load_mage.php';

cli_only();
$dry = scriptArgs('dry') ? 'dry' : '';

$now = time();
$dateFilter = date('Y-m-d H:i:s', $now);
$orders = Mage::getModel('sales/order')->getCollection()
    ->addFieldToFilter('status', 'complete');


function getExistingOrderIds()
{
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $select = new Zend_Db_Select($db);
    $select->from('itemreport','order_id');
    return $db->fetchCol($select);
}

function addItemreportRecord($record)
{
    $db = Mage::getSingleton('core/resource')->getConnection('core_write');
    $table = new Zend_Db_Table(array('db' => $db, 'name' => 'itemreport'));
    $row = $table->createRow($record);
    $row->save();
}

$exOrders = getExistingOrderIds();
$gsId = (int)Mage::getConfig()->getNode('localconf/modules/catalog/category/giftshop_cat_id');


$testorders = array(
//    '100000040',
//    '100000046', 
//    '100000074'
);
foreach($orders as $order) {
    //var_dump($order->toArray());
    if( in_array($order->getId(), $exOrders)) continue;
    if ($testorders and ! in_array($order->getIncrementId(), $testorders)) continue;
    $items = $order->getAllItems();
    $records = array();
    foreach($items as $item) {
        $record = array(
            'item_id' => NULL,
            'item_name' => NULL,
            'order_id' => NULL,
            'order_date' => NULL,
            'order_number' => NULL,
            'category_name' => NULL,
            'category_id' => NULL,
            'item_cart_price' => NULL,
            'item_qty'  => NULL,
            'item_shipping_quota' => NULL,
            'item_promo_quota' => NULL,
            'item_revenue' => NULL,
        );
        $idata = $item->toArray();
        $record['item_id'] = $idata['item_id'];
        $record['item_name'] = $idata['name'];
        $record['item_qty'] = $idata['qty_ordered'];
        $record['item_cart_price'] = $idata['base_price_incl_tax'];
        $product = $item->getProduct();
        $catIds = $product->getCategoryIds();
        $rCat = '';
        $rCatId = 0;
        foreach($catIds as $catId) {
            $cat = Mage::getModel('catalog/category')->load($catId);
            if($cat->getName() == 'Sales') continue;
            if($gsId and $cat->getId() == $gsId) continue;
            $rCat = $cat->getName();
            $rCatId = $cat->getId();
            break;
        }
        $record['category_name'] = $rCat;
        $record['category_id'] = $rCatId;
        $records[] = $record;
    }
    $odata = $order->toArray();
    //var_dump($odata);
    $shipping =  abs((float)$odata['base_shipping_incl_tax']);
    $paid = abs((float)$odata['grand_total']);
    $discount = abs((float)$odata['discount_amount']);
    $credit = abs((float)$odata['customer_credit_amount']);
    $promo = $discount + $credit;
    $rcount = count($records);
    $itempromo = number_format($promo/$rcount,2);
    $itemshipping = number_format($shipping/$rcount,2);
    foreach($records as $record) {
        $record['order_id'] = $order->getId();
        $record['order_number'] = $order->getIncrementId();
        $record['order_date'] = $order->getCreatedAt();
        $record['item_shipping_quota'] = $itemshipping;
        $record['item_promo_quota'] = $itempromo;
        $record['item_revenue'] = number_format( $record['item_cart_price']*$record['item_qty'] + $itemshipping - $itempromo ,2);
        addItemreportRecord($record);
    }
}

