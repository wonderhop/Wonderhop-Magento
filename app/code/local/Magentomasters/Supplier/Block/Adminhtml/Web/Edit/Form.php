<?php

class Magentomasters_Supplier_Block_Adminhtml_Web_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $action = $this->getRequest()->getActionName();
        if($action == 'config'){
            $saveUrl = "*/*/saveconfig";
        }else{
            $saveUrl = "*/*/save";
        }

        $form = new Varien_Data_Form(array(
                        'id' => 'edit_form',
                        'action' => $this->getUrl($saveUrl, array('id' => $this->getRequest()->getParam('id'))),
                        'method' => 'post',
                        'enctype' => 'multipart/form-data'
                )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}