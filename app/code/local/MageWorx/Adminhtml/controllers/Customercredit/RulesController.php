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
class MageWorx_Adminhtml_Customercredit_RulesController extends Mage_Adminhtml_Controller_Action
{
    protected function _initRule() {
        Mage::register('current_customercredit_rule', Mage::getModel('customercredit/rules'));
        if ($id = (int) $this->getRequest()->getParam('id')) {
            Mage::registry('current_customercredit_rule')
                ->load($id);
        }
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo');
        return $this;
    }

    public function indexAction() {        
        $this->_title($this->__('Credit'))->_title($this->__('Manage Rules'));
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('mageworx/customercredit_rules'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('customercredit/rules');

        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('salesrule')->__('This rule no longer exists'));
                $this->_redirect('*/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $model->getActions()->setJsFormObject('rule_actions_fieldset');

        Mage::register('current_customercredit_rule', $model);        
        
        $block = $this->getLayout()->createBlock('mageworx/customercredit_rules_edit')
            ->setData('action', $this->getUrl('*/*/save'));

        $name = $model->getName()?$model->getName():$this->__('New Rule');
        $this->_title($this->__('Credit'))->_title($this->__('Manage Rules'))->_title($name);
            
        $this->_initAction();
        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->setCanLoadRulesJs(true);

        $this->_addContent($block)
            ->_addLeft($this->getLayout()->createBlock('mageworx/customercredit_rules_edit_tabs'))
            ->renderLayout();

    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('customercredit/rules');

                if ($id = $this->getRequest()->getParam('rule_id')) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('salesrule')->__('Wrong rule specified.'));
                    }
                }

                
                
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                                
                unset($data['rule']);
                $model->loadPost($data);

                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());
                $model->save();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('salesrule')->__('Rule was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setPageData(false);
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('customercredit/rules');
                $model->load($id);
                $model->delete();
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('salesrule')->__('Rule was successfully deleted'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('salesrule')->__('Unable to find a page to delete'));
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];                
        

        if ($type=='catalogrule/rule_condition_product') {
            $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('catalogrule/rule'))
                ->setPrefix('conditions');            
        } else {
            $model = Mage::getModel($type)
                ->setId($id)
                ->setType($type)
                ->setRule(Mage::getModel('salesrule/rule'))
                ->setPrefix('conditions');
        }        
        
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
        
    }

    public function gridAction()
    {
        $this->_initRule();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mageworx/customercredit_rules_grid', 'customercredit.rules.grid')
            	->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customercredit/rules');
    }

}
