<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Block_JsAlt extends Fooman_Jirafe_Block_Js
{

    /**
     * Set default template
     *
     */
    protected function _construct ()
    {
        $this->setTemplate('fooman/jirafe/js-alt.phtml');
    }

    public function getTrackingCode()
    {
        $urlHttps = 'https://'.$this->getPiwikBaseURL().'/';
        $siteId = Mage::helper('foomanjirafe')->getStoreConfig('site_id', Mage::app()->getStore()->getId());
    
        return <<<EOF
<!-- Jirafe:START -->
<noscript><p><img src="{$urlHttps}piwik.php?idsite={$siteId}" style="border:0" alt="" /></p></noscript>
<!-- Jirafe:END -->
EOF;
    }

}
