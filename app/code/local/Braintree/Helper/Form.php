<?php

class Braintree_Helper_Form extends Mage_Core_Helper_Abstract
{
    public function countrySelect($name, $default='')
    {
        $str = "<select name='$name' id ='billing_address_country'>\n";
        $str .= "\t<option value=''>Please select a country</option>\n";

        $countries = Mage::getModel('directory/country_api')->items();

        foreach ($countries as $country)
        {
            $selected = ($default == $country['country_id']) ? 'selected' : '';
            $str .= "\t<option value='{$country['country_id']}' $selected>{$country['name']}</option>\n";
        }

        return $str . "</select>\n";
    }
}
