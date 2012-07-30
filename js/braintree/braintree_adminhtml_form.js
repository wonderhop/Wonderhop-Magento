function braintree_adminhtml_init (encryption_key)
{
  var braintree = Braintree.create(encryption_key);

  // Client-side-encrypt after form validation but before form submission
  editForm.validator = new Validation(editForm.formId, {
    onElementValidate : editForm.checkErrors.bind(editForm),
    onFormValidate : function(isValid, theForm) {
      if (isValid) {
        theForm.select('input[data-encrypt=true]').each(function(element) {
          element.setValue(braintree.encrypt(element.getValue()));
        });
      }
    }
  });
}
