<?php

class Braintree_Model_Source_Environment
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'development',
                'label' => 'Development'
            ),
            array(
                'value' => 'sandbox',
                'label' => 'Sandbox',
            ),
            array(
                'value' => 'production',
                'label' => 'Production'
            )
        );
    }
}
