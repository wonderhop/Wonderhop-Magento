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

class Fooman_Jirafe_Adminhtml_JirafeController extends Mage_Adminhtml_Controller_Action
{

    protected function _construct()
    {
        $this->setUsedModuleName('Fooman_Jirafe');
    }

    public function reportAction()
    {
        if (Mage::helper('foomanjirafe')->isConfigured()) {
            Mage::getModel('foomanjirafe/report')->cron();
        } else {
            Mage::getModel('foomanjirafe/report')->cron();
        }
        $this->_redirect('adminhtml/system_config/edit/section/foomanjirafe');
    }

    public function syncAction()
    {
        $jirafe = Mage::getModel('foomanjirafe/jirafe');
        $jirafe->syncUsersStores();
        $this->_redirect('adminhtml/system_config/edit/section/foomanjirafe');
    }

    public function resetAction()
    {
        $jirafe = Mage::helper('foomanjirafe/setup');
        $jirafe->resetDb();
        $this->_redirect('adminhtml/system_config/edit/section/foomanjirafe');
    }

    public function toggleDashboardAction()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $active = $user->getJirafeDashboardActive();
        $user->setJirafeDashboardActive(!$active);
        //to prevent a password change unset it here for pre 1.4.0.0
        if (version_compare(Mage::getVersion(), '1.4.0.0') < 0) {
            $user->unsPassword();
        }
        $user->save();
        $this->_redirect('adminhtml/dashboard');
    }

    public function optinAction()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $answer = $this->getRequest()->getParam('answer') == 'yes' ? true : false;
        $user->setJirafeEnabled($answer);
        $user->setJirafeSendEmail($answer);
        $user->setJirafeDashboardActive($answer);
        $user->setJirafeOptinAnswered(true);
        //to prevent a password change unset it here for pre 1.4.0.0
        if (version_compare(Mage::getVersion(), '1.4.0.0') < 0) {
            $user->unsPassword();
        }
        $user->save();

        $helper = Mage::helper('foomanjirafe/data');
        if ($answer) {
            $notice = $helper->__('You now have access to advanced analytics from Jirafe.  Please check your email at \'%s\' to confirm your email address.',
                $user->getEmail()
            );
        } else {
            $notice = $helper->__('You will not have access to advanced analytics from Jirafe.  If you would like to enable this in the future, please choose \'%1$s\' in your \'%2$s\' settings.',
                $helper->__('Enable Jirafe'),
                $helper->__('My Account')
            );
        }

        Mage::getSingleton('adminhtml/session')->addNotice($notice);
        $this->_redirect('adminhtml/dashboard');
    }
}