function braintree_frontend_init (encryption_key)
{
  braintree = Braintree.create(encryption_key);
  Review.prototype.save = function () {

    // encrypt all fields
    var encrypted_form = $(payment.form).clone(true);

    encrypted_form.select('input, select').each(function (element) {
      var old_value = $$('#' + payment.form + ' #' + element.getAttribute('id'))[0].getValue();
      element.setAttribute('id', 'encrypted_' + element.getAttribute('id'));

      if (element.readAttribute('data-encrypt') === 'true')
        {
          var encrypted_value = braintree.encrypt(old_value);
          element.setValue(encrypted_value);
        }
        else
          {
            element.setValue(old_value);
          }
    });

    if (checkout.loadWaiting!=false) return;
    checkout.setLoadWaiting('review');
    var params = Form.serialize(encrypted_form);
    if (this.agreementsForm) {
      params += '&'+Form.serialize(this.agreementsForm);
    }
    params.save = true;
    var request = new Ajax.Request(
      this.saveUrl,
      {
        method:'post',
        parameters:params,
        onComplete: this.onComplete,
        onSuccess: this.onSave,
        onFailure: checkout.ajaxFailure.bind(checkout)
      }
    );
  }
}


