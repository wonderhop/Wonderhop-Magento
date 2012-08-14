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

class MageWorx_Adminhtml_Customercredit_CodeController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Action initialization 
	 */
	protected function _initAction() {
            $this->loadLayout();
            $this->_setActiveMenu('promo');
	}
	/**
	 * Default Action
	 */
	public function indexAction() {
            $this->_title($this->__('Recharge Codes'))->_title($this->__('Manage'));
            
	    $block = $this->getLayout()->createBlock('mageworx/customercredit_code');
		$this->_initAction();
		$this->_addContent($block)
		  ->renderLayout();
	}
	
	public function gridAction()
	{
	    $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mageworx/customercredit_code_grid', 'customercredit.code.grid')
                ->toHtml()
        );
	}
	
	public function newAction()
	{
	    $this->_forward('edit');
	}
	
	public function editAction() {
        try {
    	   $code = $this->_initCode();
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                if (!empty($data['settings']))
                    $code->addData($data['settings']);
                if (!empty($data['details']))
                    $code->addData($data['details']);
                if (!empty($data['use_config']))
                {
                    foreach ($data['use_config'] as $config => $value)
                    {
                        $code->setData($config.'_use_config', $value);
                    }
                }
            }
            $block = $this->getLayout()->createBlock('mageworx/customercredit_code_edit');
            
            $this->_title($this->__('Recharge Codes'))->_title($this->__('Manage'));
            
            $this->_initAction();
            $this->_addContent($block)
                ->_addLeft($this->getLayout()->createBlock('mageworx/customercredit_code_edit_tabs'))
                ->renderLayout();
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*');
            return false;
        }
    }
    
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $dataDetails = $this->getRequest()->getPost('details');
                $dataDetails = $this->_filterDates($dataDetails, array('from_date', 'to_date'));
                $data['details'] = $dataDetails;
            
                $codeModel = $this->_initCode();
                $validateResult = $codeModel->validateData(new Varien_Object($data['details']));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setFormData($data);
                    $this->_redirect('*/*/edit', array('id'=>$codeModel->getId()));
                    return;
                }
                $codeModel->loadPost($data);
                if ($codeModel->getIsNew())
                {
                    $codeModel->generate();
                    $successMessage = $this->_helper()->__('%d Recharge Code(s) was successfully generated', $codeModel->getData('generate','qty'));
                }
                else 
                {
                    $codeModel->save();
                    $successMessage = $this->_helper()->__('Recharge Code was successfully saved');
                }

                Mage::getSingleton('adminhtml/session')->addSuccess($successMessage);
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                $this->_redirect('*/*/');
                return true;
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return false;
            }
        }
    }
    
    public function logGridAction()
    {
        try {
            $code = $this->_initCode(false);
            $this->_initAction();
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('mageworx/customercredit_code_edit_tab_log')->toHtml()
            );
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*');
            return false;
        }
    }
    
    public function deleteAction()
    {
        try {
            $code = $this->_initCode(false);
            if ($code->isDeletable()) {
                $code->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->_helper()->__('Code was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            else {
                Mage::throwException($this->_helper()->__('Recharge Code can not be deleted.'));
            }
        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            if ($id = $code->getId()) {
                $this->_redirect('*/*/edit', array('id' => $id));
            }
            else {
                $this->_redirect('*/*');
            }
            return false;
        }
    }
    
    /**
     * Initialize code from request parameters
     *
     * @return MageWorx_CustomerCredit_Model_Code
     */
    protected function _initCode($bInitNew = true)
    {
    	$codeId    = (int) $this->getRequest()->getParam('id');
        $codeModel = Mage::getModel('customercredit/code');
        $bWrongCode = false;
        if (!$codeId && $bInitNew)
        {
            $codeModel->setIsNew(true);
        }
        elseif ($codeId)
        {
            $codeModel->load($codeId);
            if ($codeModel->getId() != $codeId)
            {
                $bWrongCode = true;
            }
            
        }
        else // $bInitNew == false
        {
            $bWrongCode = true;
        }
        if ($bWrongCode)
        {
            Mage::throwException($this->_helper()->__('Wrong Recharge Code specified.'));
            //Mage::getSingleton('adminhtml/session')->addError($this->_helper()->__('Wrong Recharge Code specified.'));
            //$this->_redirect('*/*');
            //return;
        }
        Mage::register('current_customercredit_code', $codeModel);
        return $codeModel;
    }
    
    /**
     * 
     * @return MageWorx_CustomerCredit_Helper_Data
     */
    protected function _helper()
    {
    	return Mage::helper('customercredit');
    }
}