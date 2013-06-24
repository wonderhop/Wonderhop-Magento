<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Email extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
	  $fieldset = $form->addFieldset('form_email', array('legend'=>Mage::helper('supplier')->__('Email Settings')));	  
	  $id = $this->getRequest()->getParam('id');
      if (isset($id)) {
          $nameDisabled = true;
		  $passwd = false;
      } else {
          $nameDisabled = false;
		  $passwd = true;
      }
	  
	  $fieldset->addField('email_enabled', 'select', array(
                'label'     => Mage::helper('supplier')->__('Email Enabled'),
                'name'      => 'email_enabled',
                'values'    => array(
                        array(
                                'value'     => 0,
                                'label'     => Mage::helper('supplier')->__('Disabled'),
                        ),
                        array(
                                'value'     => 1,
                                'label'     => Mage::helper('supplier')->__('Enabled'),
                        ),
                ),
      ));
	  
	  $fieldset->addField('surname', 'text', array(
          'label'     => Mage::helper('supplier')->__('Name'),
          'name'      => 'surname'
	  ));
	  
	  $fieldset->addField('email1', 'text', array(
          'label'     => Mage::helper('supplier')->__('First E-Mail Address'),
          'name'      => 'email1',
          'class'     => 'validate-email'
      ));

      $fieldset->addField('email2', 'text', array(
          'label'     => Mage::helper('supplier')->__('Second E-Mail Address'),
          'name'      => 'email2',
          'class'     => 'validate-email',
      ));
	  
	  $options = Mage::getModel('supplier/supplier')->getEmailTemplates();
			
	  	$fieldset->addField('email_template', 'select', array(
					'label'     => Mage::helper("supplier")->__("Email Template"),
					'title'     => Mage::helper("supplier")->__("Email Template"),
					'name'      => 'email_template',
					'options'   => $options,
		));
	  
	  $fieldset->addField('email_header', 'text', array(
          'label'     => Mage::helper('supplier')->__('Email header'),
          'name'      => 'email_header',
		  'note'	  => "Overwrites default setting if filled." 
        ));
		
	$fieldset->addField('email_message', 'textarea', array(
	  'label'     => Mage::helper('supplier')->__('Email message'),
	  'name'      => 'email_message',
	  'note'	  => "Overwrites default setting if filled." 
	));
	
	  $fieldset->addField('pdf_enabled', 'select', array(
                'label'     => Mage::helper('supplier')->__('PDF Enabled'),
                'name'      => 'pdf_enabled',
                'values'    => array(
                        array(
                                'value'     => 0,
                                'label'     => Mage::helper('supplier')->__('Disabled'),
                        ),
                        array(
                                'value'     => 1,
                                'label'     => Mage::helper('supplier')->__('Enabled'),
                        ),
                ),
      ));	
	
	  $fieldset->addField('pdf_header', 'text', array(
          'label'     => Mage::helper('supplier')->__('Pdf header'),
          'name'      => 'pdf_header',
		  'note'	  => "Overwrites default setting if filled." 
        ));
		
		$fieldset->addField('pdf_message', 'textarea', array(
          'label'     => Mage::helper('supplier')->__('Pdf message'),
          'name'      => 'pdf_message',
		  'note'	  => "Overwrites default setting if filled." 
        ));
	  
	  	$fieldset->addField('pdf_template', 'select', array(
					'label'     => Mage::helper("supplier")->__("PDF Template"),
					'title'     => Mage::helper("supplier")->__("PDF Template"),
					'name'      => 'pdf_template',
					'options'   => $options,
		));

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