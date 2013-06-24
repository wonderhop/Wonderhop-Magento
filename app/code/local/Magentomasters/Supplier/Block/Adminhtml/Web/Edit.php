<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'supplier';
        $this->_controller = 'adminhtml_web';
        
        $this->_updateButton('save', 'label', Mage::helper('supplier')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('supplier')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->removeButton('delete');
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('web_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'web_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'web_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('web_data') && Mage::registry('web_data')->getId() )
        {
            return Mage::helper('supplier')->__("Edit Supplier '%s'", $this->htmlEscape(Mage::registry('web_data')->getName()));
        }
        else
        {
            return Mage::helper('supplier')->__('Add Item');
        }
    }
}