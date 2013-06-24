<?php
class Magentomasters_Supplier_Block_History extends Magentomasters_Supplier_Block_Abstract{
		
	public function getStatuses()
    {
        $state = $this->getOrder()->getState();
        $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
        return $statuses;
    }

    public function canSendCommentEmail()
    {
        return Mage::helper('sales')->canSendOrderCommentEmail($this->getOrder()->getStore()->getId());
    }

    /**
     * Retrieve order model
     *
     * @return Mage_Sales_Model_Order
     */

    public function canAddComment()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/comment') &&
               $this->getOrder()->canComment();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/addComment', array('order_id'=>$this->getOrder()->getId()));
    }

    /**
     * Customer Notification Applicable check method
     *
     * @param  Mage_Sales_Model_Order_Status_History $history
     * @return boolean
     */
    public function isCustomerNotificationNotApplicable(Mage_Sales_Model_Order_Status_History $history)
    {
        return $history->isCustomerNotificationNotApplicable();
    }	
}
	