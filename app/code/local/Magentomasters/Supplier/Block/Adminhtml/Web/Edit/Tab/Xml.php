<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Tab_Xml extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
	  $fieldset = $form->addFieldset('form_xml', array('legend'=>Mage::helper('supplier')->__('Xml/Csv Settings')));	  
	  $id = $this->getRequest()->getParam('id');
      if (isset($id)) {
          $nameDisabled = true;
		  $passwd = false;
      } else {
          $nameDisabled = false;
		  $passwd = true;
      }
	  
	  $fieldset->addField('xml_enabled', 'select', array(
                'label'     => Mage::helper('supplier')->__('Xml/Csv Enabled'),
                'name'      => 'xml_enabled',
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
	   
	   $fieldset->addField('xml_csv', 'select', array(
	        'label'     => Mage::helper('supplier')->__('Xml or Csv'),
	        'name'      => 'xml_csv',
	        'values'    => array(
	                array(
	                        'value'     => 0,
	                        'label'     => Mage::helper('supplier')->__('XML'),
	                ),
	
	                array(
	                        'value'     => 1,
	                        'label'     => Mage::helper('supplier')->__('CSV'),
	                    ),
	            ),
	   ));
	   

      $fieldset->addField('xml_name', 'text', array(
          'label'     => Mage::helper('supplier')->__('Xml/Csv Name'),
          'class'     => 'validate-code',
          'name'      => 'xml_name',
      ));
	  
	  $supplier = Mage::registry('web_data');
      $url = substr(Mage::getBaseUrl(),0,strlen(Mage::getBaseUrl()) - 10).'var/export/supplier/'.$supplier->getData('id') . '/';
		
	  $fieldset->addField('', 'note', array(
            'label'     => Mage::helper('supplier')->__('Xml/Csv folder'),
            'text'     => $url,
			'note'	  => "Check if the folder cannot be accessed publicly." 
      ));
	  
	  $fieldset->addField('xml_type', 'select', array(
                'label'     => Mage::helper('supplier')->__('Xml/Csv options'),
                'name'      => 'xml_type',
                'values'    => array(
                        array(
                                'value'     => 0,
                                'label'     => Mage::helper('supplier')->__('Single File'),
                        ),

                        array(
                                'value'     => 1,
                                'label'     => Mage::helper('supplier')->__('File per order'),
                        ),
                ),
      ));
	  
	  $options = Mage::getModel('supplier/supplier')->getEmailTemplates();
	  
	  $fieldset->addField('xml_template', 'select', array(
				'label'     => Mage::helper("supplier")->__("Xml/Csv Template"),
				'title'     => Mage::helper("supplier")->__("Xml/Csv Template"),
				'name'      => 'xml_template',
				'options'   => $options,
		));
	
	 $fieldset->addField('csv_delimeter', 'text', array(
				'label'     => Mage::helper("supplier")->__("Csv Delimeter"),
				'title'     => Mage::helper("supplier")->__("Csv Delimeter"),
				'name'      => 'csv_delimeter',
				'note'	  => "Default is ," 
		));	
	  
	  $fieldset->addField('xml_ftp', 'select', array(
                'label'     => Mage::helper('supplier')->__('FTP upload'),
                'name'      => 'xml_ftp',
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
	  
	  $fieldset->addField('xml_ftp_type', 'select', array(
                'label'     => Mage::helper('supplier')->__('FTP active/passive'),
                'name'      => 'xml_ftp_type',
                'values'    => array(
                        array(
                                'value'     => 0,
                                'label'     => Mage::helper('supplier')->__('Active'),
                        ),

                        array(
                                'value'     => 1,
                                'label'     => Mage::helper('supplier')->__('Passive'),
                        ),
                ),
      ));
	  
	  $fieldset->addField('xml_ftp_host', 'text', array(
          'label'     => Mage::helper('supplier')->__('FTP Host'),
          'name'      => 'xml_ftp_host',
      ));
	  
	  $fieldset->addField('xml_ftp_port', 'text', array(
          'label'     => Mage::helper('supplier')->__('FTP Port'),
          'name'      => 'xml_ftp_port',
      ));
	  
	  $fieldset->addField('xml_ftp_user', 'text', array(
          'label'     => Mage::helper('supplier')->__('FTP User'),
          'name'      => 'xml_ftp_user',
      ));
	  
	  $fieldset->addField('xml_ftp_password', 'text', array(
          'label'     => Mage::helper('supplier')->__('FTP Password'),
          'name'      => 'xml_ftp_password',
      ));
	  
	  $fieldset->addField('xml_ftp_path', 'text', array(
          'label'     => Mage::helper('supplier')->__('FTP Path'),
          'name'      => 'xml_ftp_path',
          'note'	  => "Always end with /"
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