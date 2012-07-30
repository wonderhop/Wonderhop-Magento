<?php

class Braintree_Helper_Messages extends Mage_Core_Helper_Abstract
{
    public function success()
    {
        $messages = Mage::getSingleton('customer/session')->getMessages();

        if (!count($messages->getItems()))
        {
            return '';
        }

        $str = "<div class='success'>\n\t<p>";

        foreach ($messages->getItems() as $message)
        {
            $str .= "{$message->getText()}\n";
        }

        $messages->clear();

        return $str . "\t</p>\n</div>\n";
    }

    public function errors($errors)
    {
        $str = "<ul class='error'>\n";
        foreach ($errors as $error)
        {
            $str .= "\t<li>$error</li>\n";
        }

        return $str . '</ul>';
    }
}
