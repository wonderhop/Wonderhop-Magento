<?php
class Sp_Ajaxify_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getMessageUrl($relative = false)
	{
		return $relative ? '/ajaxify/index/message' : Mage::getUrl('ajaxify/index/message');
	}
}
