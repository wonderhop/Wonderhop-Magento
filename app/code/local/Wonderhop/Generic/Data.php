<?php

class Wonderhop_Generic_Data {
    
    protected static $_model;
    
    public static function model()
    {
        if ( ! self::$_model) self::$_model = Mage::getSingleton('generic/data');
        return self::$_model;
    }
    
    public static function __callStatic($name, $args)
    {
        return call_user_func_array( array(self::model(), $name) , $args);
    }

}
