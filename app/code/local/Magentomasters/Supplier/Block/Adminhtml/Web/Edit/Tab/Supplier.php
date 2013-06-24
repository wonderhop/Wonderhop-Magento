<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Supplier extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
	  $fieldset = $form->addFieldset('web_form', array('legend'=>Mage::helper('supplier')->__('Supplier information')));	  
	  $id = $this->getRequest()->getParam('id');
      if (isset($id)) {
          $nameDisabled = true;
		  $passwd = false;
      } else {
          $nameDisabled = false;
		  $passwd = true;
      }
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('supplier')->__('Supplier Code'),
          'class'     => 'validate-code',
          'required'  => true,
          'name'      => 'name',
		  'disabled'  => $nameDisabled
      ));
	  
	  $fieldset->addField('username', 'text', array(
          'label'     => Mage::helper('supplier')->__('Username / Emailadres'),
          'class'     => 'required-entry validate-email',
          'required'  => true,
          'name'      => 'username',
	  ));

      $fieldset->addField('password-empty', 'password', array(
          'label'     => Mage::helper('supplier')->__('Password'),
          'name'      => 'password',
          'required'  => $passwd,
          'class'     => 'validate-password',
      ));
	  
	  $fieldset->addField('company', 'text', array(
          'label'     => Mage::helper('supplier')->__('Company'),
          'name'      => 'company',
	  ));
	  
	  $fieldset->addField('contact', 'text', array(
          'label'     => Mage::helper('supplier')->__('Contact'),
          'name'      => 'contact',
	  ));
	  
	  $fieldset->addField('phone', 'text', array(
          'label'     => Mage::helper('supplier')->__('Phone'),
          'name'      => 'phone',
	  ));
	  
	  $fieldset->addField('address1', 'text', array(
          'label'     => Mage::helper('supplier')->__('Street'),
          'name'      => 'address1',
	  ));
	  
	  $fieldset->addField('address2', 'text', array(
          'label'     => Mage::helper('supplier')->__('Housenumber'),
          'name'      => 'address2',
	  ));
	  
	  $fieldset->addField('city', 'text', array(
          'label'     => Mage::helper('supplier')->__('City'),
          'name'      => 'city',
	  ));
	  
	  $fieldset->addField('postalcode', 'text', array(
          'label'     => Mage::helper('supplier')->__('Postalcode'),
          'name'      => 'postalcode',
	  ));
	  	  
	  $fieldset->addField('country', 'select', array(
			'name'  => 'country',
			'label'     => Mage::helper('supplier')->__('Country'),
			'values'    => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
	  ));
		
	  $fieldset->addField('state', 'text', array(
          'label'     => Mage::helper('supplier')->__('Provincie'),
          'name'      => 'state',
	  ));
	  
	  $fieldset->addField('custom1', 'text', array(
          'label'     => Mage::helper('supplier')->__('Custom 1'),
          'name'      => 'custom1',
	  ));
	  
	  $fieldset->addField('custom2', 'text', array(
          'label'     => Mage::helper('supplier')->__('Custom 2'),
          'name'      => 'custom2',
	  ));
	  
	  $fieldset->addField('custom3', 'text', array(
          'label'     => Mage::helper('supplier')->__('Custom 3'),
          'name'      => 'custom3',
	  ));
	  
	  $fieldset->addField('custom4', 'text', array(
          'label'     => Mage::helper('supplier')->__('Custom 4'),
          'name'      => 'custom4',
	  ));
	  
		//$fieldset->addField('schedule', 'text', array(
		//	'name' => 'schedule',
		//	'value' => 'schedule'
		//));		  

		//$fieldset->addField('show_custom_attr', 'select', array(
		//          'label'     => Mage::helper('supplier')->__('Show attributes email and xml'),
		//          'name'      => 'show_custom_attr',
		//          'values'    => array(
		//                  array(
		//                         'value'     => 0,
		//                          'label'     => Mage::helper('supplier')->__('Disabled'),
		//                  ),
		//
		//                array(
		//                        'value'     => 1,
		//                        'label'     => Mage::helper('supplier')->__('Enabled'),
		//                ),
		//        ),
		//));
		
		//$fieldset->addField('attributes', 'text', array(
		//  'label'     => Mage::helper('supplier')->__('Selected Attributes'),
		//  'name'      => 'attributes',
		//  'note'	  => "Commaseperated the ADMIN LABELS of the attributes. Example Supplier,Status. Overwrites default settings." 
		//));

      if ( Mage::getSingleton('adminhtml/session')->getWebData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWebData());
          Mage::getSingleton('adminhtml/session')->setWebData(null);
      } elseif ( Mage::registry('web_data') ) {
          $form->setValues(Mage::registry('web_data')->getData());
      }
      return parent::_prepareForm();
  }

	
 	

}