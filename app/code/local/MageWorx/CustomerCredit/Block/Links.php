<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Customer Credit extension
 *
 * @category   MageWorx
 * @package    MageWorx_CustomerCredit
 * @author     MageWorx Dev Team
 */

if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
    // for magento 1.4.2.0 and above
    class MageWorx_CustomerCredit_Block_Links extends Mage_Page_Block_Template_Links_Block
    {
        /**
         * Position in link list
         * @var int
         */
        protected $_position = 40;

        /**
         * Set link title, label and url
         */
        public function __construct() {
            parent::__construct();
            if ($this->helper('customercredit')->isShowCustomerCredit()) {       

                $text = $this->__('My Credit');            
                $this->_label = $text;
                $this->_title = $text;
                $this->_url = $this->getUrl('customercredit');
            }
        }
    }
} else {
    // for magento 1.4.0.x-1.4.1.x
    class MageWorx_CustomerCredit_Block_Links extends Mage_Core_Block_Template
    {
        public function addCustomercreditLink() {
            $parentBlock = $this->getParentBlock();
            if ($parentBlock && $this->helper('customercredit')->isShowCustomerCredit()) {
                $text = $this->__('My Credit');            
                $parentBlock->addLink($text, 'customercredit', $text, true, array(), 40, null, 'class="top-link-customercredit"');
            }
            return $this;
        }  
    }   
}
