<?php

class Fooman_Jirafe_Block_Adminhtml_System_Account_Edit_Form extends Mage_Adminhtml_Block_System_Account_Edit_Form
{

    protected function _prepareForm ()
    {
        parent::_prepareForm();
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        $form = $this->getForm();
        $fieldset = $form->addFieldset('jirafe', array('legend' => Mage::helper('adminhtml')->__('Jirafe Analytics')));

        $yesNo = array();
        $yesNo[] = array('label' => Mage::helper('foomanjirafe')->__('Yes'), 'value' => 1);
        $yesNo[] = array('label' => Mage::helper('foomanjirafe')->__('No'), 'value' => 0);

        $fieldset->addField('jirafe_enabled', 'select', array(
            'name' => 'jirafe_enabled',
            'label' => Mage::helper('foomanjirafe')->__('Enable Jirafe'),
            'title' => Mage::helper('foomanjirafe')->__('Enable Jirafe'),
            'required' => false,
            'values' => $yesNo,
            'value' => $adminUser->getJirafeEnabled()
        ));

        $fieldset->addField('jirafe_send_email', 'select', array(
            'name' => 'jirafe_send_email',
            'label' => Mage::helper('foomanjirafe')->__('Send Jirafe Emails'),
            'title' => Mage::helper('foomanjirafe')->__('Send Jirafe Emails'),
            'required' => false,
            'values' => $yesNo,
            'value' => $adminUser->getJirafeSendEmail()
        ));

        $fieldset->addField('jirafe_dashboard_active', 'select', array(
            'name' => 'jirafe_dashboard_active',
            'label' => Mage::helper('foomanjirafe')->__('Display Jirafe Dashboard'),
            'title' => Mage::helper('foomanjirafe')->__('Display Jirafe Dashboard'),
            'after_element_html' => '<p class="nm"><small>' . Mage::helper('foomanjirafe')->__('Displays the Jirafe Dashboard instead of the default Magento dashboard') . '</small></p>',
            'required' => false,
            'values' => $yesNo,
            'value' => $adminUser->getJirafeDashboardActive()
        ));

        /* We don't yet individually map store to user
        $fieldset->addField('jirafe_send_email_for_store', 'multiselect', array(
            'name' => 'jirafe_send_email_for_store[]',
            'label' => Mage::helper('foomanjirafe')->__('Email Daily Report for Store'),
            'title' => Mage::helper('foomanjirafe')->__('Email Daily Report for Store'),
            'after_element_html' => '<p class="nm"><small>' . Mage::helper('foomanjirafe')->__('Hold down the Shift key to select multiple stores') . '</small></p>',
            'required' => false,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false),
            'value' => explode(",", $adminUser->getJirafeSendEmailForStore())
        ));
         */

        $reportTypes = array();
        $reportTypes[] = array('label' => Mage::helper('foomanjirafe')->__('Simple'), 'value' => 'simple');
        $reportTypes[] = array('label' => Mage::helper('foomanjirafe')->__('Detail'), 'value' => 'detail');

        $fieldset->addField('jirafe_email_report_type', 'select', array(
            'name' => 'jirafe_email_report_type',
            'label' => Mage::helper('foomanjirafe')->__('Email Report Type'),
            'title' => Mage::helper('foomanjirafe')->__('Email Report Type'),
            'after_element_html' => '<p class="nm"><small>' . Mage::helper('foomanjirafe')->__('Detail adds gross sales, refunds, discounts to the report') . '</small></p>',
            'required' => false,
            'values' => $reportTypes,
            'value' => $adminUser->getJirafeEmailReportType()
        ));

        $fieldset->addField('jirafe_email_suppress', 'select', array(
            'name' => 'jirafe_email_suppress',
            'label' => Mage::helper('foomanjirafe')->__('Suppress Emails With No Data'),
            'title' => Mage::helper('foomanjirafe')->__('Suppress Emails With No Data'),
            'after_element_html' => '<p class="nm"><small>' . Mage::helper('foomanjirafe')->__('Save virtual trees if you have lots of stores with no daily orders') . '</small></p>',
            'required' => false,
            'values' => $yesNo,
            'value' => $adminUser->getJirafeEmailSuppress()
        ));

        if (version_compare(Mage::getVersion(), '1.4.0.0', '>=')) {
            $this->setChild('form_after',
                $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                    ->addFieldMap("jirafe_enabled", 'jirafe_enabled')
                    ->addFieldMap("jirafe_send_email", 'jirafe_send_email')
                    ->addFieldMap("jirafe_email_report_type", 'jirafe_email_report_type')
                    ->addFieldMap("jirafe_dashboard_active", 'jirafe_dashboard_active')
                    ->addFieldMap("jirafe_email_suppress", 'jirafe_email_suppress')
                    ->addFieldDependence('jirafe_send_email', 'jirafe_enabled', '1')
                    ->addFieldDependence('jirafe_email_report_type', 'jirafe_enabled', '1')
                    ->addFieldDependence('jirafe_dashboard_active', 'jirafe_enabled', '1')
                    ->addFieldDependence('jirafe_email_suppress', 'jirafe_enabled', '1')
            );
        }

        return $this;
    }

}
