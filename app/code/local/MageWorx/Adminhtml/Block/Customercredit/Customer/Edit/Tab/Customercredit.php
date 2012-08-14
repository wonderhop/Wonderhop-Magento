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

class MageWorx_Adminhtml_Block_Customercredit_Customer_Edit_Tab_Customercredit extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct() {
        parent::__construct();
        $this->setId('customercredit_credit');
    }

    public function getAfter() {
        return 'tags';
    }

    public function getTabLabel() {
        return Mage::helper('customercredit')->__('Internal Credit');
    }

    public function getTabTitle() {
        return Mage::helper('customercredit')->__('Internal Credit');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        if (Mage::registry('current_customer')->getId()) {
            return false;
        }
        return true;
    }

    protected function _toHtml() {
        return parent::_toHtml() . $this->getChildHtml();
    }
}