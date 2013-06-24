<?php
class Magentomasters_Supplier_Model_Redirect extends Mage_Core_Model_Abstract {
    public function checkSupplierInfo( $username, $password ) {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "
            SELECT id
            FROM " . Mage::getSingleton('core/resource')->getTableName('supplier_users') . "
            WHERE (username='$username') and (password='$password')
        ";
        $result = $connect->query($query);
        $id = $result->fetch();
        return $id;
    }
}