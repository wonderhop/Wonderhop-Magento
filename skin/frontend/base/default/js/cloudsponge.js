var importContactsFlag = false;
var inputService;
var popupWinRef; //get popup window reference
var stopImportTimeCount;
var imported; 
document.observe("dom:loaded", function() { 

    $$('.cloudsponge_select_emails').each(function(element){  
        Event.observe(element, 'click', selectCheckBoxes);
    });
    
    Event.observe('close_popup', 'click', closePopup);
 
    
    $$('.cloudsponge_yahoo').each(function(element){  
        Event.observe(element, 'click', importYahoo);
    });
    
    $$('.cloudsponge_msn').each(function(element){
        Event.observe(element, 'click', importMSN);
    });
    
    $$('.cloudsponge_gmail').each(function(element){
        Event.observe(element, 'click', importGmail);
    });
    
    $$('.cloudsponge_aol').each(function(element){
        Event.observe(element, 'click', showAOLUserPasswordForm);
    });
    
    $$('.cloudsponge_plaxo').each(function(element){
        Event.observe(element, 'click', showPlaxoUserPasswordForm);
    });
    
    $$('.cloudsponge_apple').each(function(element){
        Event.observe(element, 'click', importApple);
    });
    
 
    
    $$('.userpassword_submit').each(function(element){
        Event.observe(element, 'click', importUserPassword);
    });
    
    $$('.address_records_all').each(function(element){
        Event.observe(element, 'click', selectAllAddressRecords);
    });
    
    $$('.address_records_none').each(function(element){
        Event.observe(element, 'click', selectNoneAddressRecords);
    });
    
  
    
    // hide the address confirmation form fields
    hideAddressRecords();
});

function closePopup() {
    $('popup-overlay').hide();
    $('popup-content').hide();
}

function selectCheckBoxes() {
    var checkedList = [];
    var str = $('cloud_invite_input').value;
    if (str) {
        checkedList = str.split(/\s?,\s?/);
    }
    $$('.address_record_checkbox_input').each(function(ele){
       if( $(ele).checked && checkedList.indexOf($(ele).value) < 0)
       {    
           str = $('cloud_invite_input').value;
           if (str) {
                str += ',' + $(ele).value;
           } else {
                str = $(ele).value;
           }
            $('cloud_invite_input').value = str;
            
       }
    });
    closePopup()
    $('popup-content').addClassName(imported);
    
}

function setEmailAddressCheck() {
        if (counter >= 1) {
            $$('input.address_record_checkbox_input').each(function(s, index) {
                if (!s.checked) {
                    s.enable();
                }
            });
        }
        else {
            if (counter == 0) {
                $$('input.address_record_checkbox_input').each(function(s, index) {
                    if (!s.checked) {
                        s.disable();
                    }
                });
            }
        }
}

function increaseEmailCounter() {
    if (counter < maxRecip) {
        counter++;
        updateEmailCount();
        setEmailAddressCheck();
    }
}

function decreaseEmailCounter() {
    if (counter > 0) {
       counter--;
       updateEmailCount();
       setEmailAddressCheck();
    }
}

function updateEmailCount() {
    countDiv = $('counter_content');
    //countDiv.innerHTML = counter + " email addresses left";
}

function disableConfirm(){
    $('address_records_confirm_button').addClassName('disabled');
    $('address_records_confirm_button').setStyle('cursor: auto;');
}

function enableConfirm(){
    $('address_records_confirm_button').removeClassName('disabled');
    $('address_records_confirm_button').setStyle('cursor: pointer;');
}

function confirmRecipients() {

    var contactsInForm = new Array();
    var contacts = new Array();
    
    var selectedEmails = 0;
    // get the selected/checked records and add them to the recipients list
    ul = $$('input.address_record_checkbox_input').each(function(s, index) {
        
        // if the radio is checked, continue
        if (s.checked) {
            selectedEmails++;
            
            var addressRecordsData = new Array();
            
            s.ancestors()[1].descendants().each(function(s, index) {
                
                if (s.hasClassName('address_record_name')) {
                    addressRecordsData['name'] = s.innerHTML;
                }
                
                if (s.hasClassName('address_record_email')) {
                    addressRecordsData['email'] = s.innerHTML;
                }
            });
            
            contacts[contacts.length] = addressRecordsData;
            // keep the check one there
            ///s.checked = false;
        }
    });
    
    // if no email selected, do nothing
    if (selectedEmails == 0) {
    //    alert("No email selected, Please check at least one email recipient");
        return;
    }

    // get exist recipients list ul element
    ul = $('recipients_options');
    
    // add the first email in the recipients list to the contactsInForm array
    if ($('recipients_email').value.length) {
        contactsInForm.push($('recipients_email').value);
    }
    
    // add the rest of the emails in the recipients list to the contactsInForm array
    for (var k = 0; k < maxRecip; k++) {
        if ($('recipients_email'+k)) {
            contactsInForm.push($('recipients_email'+k).value);
        }
    }
    
    for (var i = 0; i < contacts.length; i++) {

        // do nothing if the recipients reach maximum
        if (recipCount >= maxRecip && maxRecip != 0) {
            disableConfirm();
            continue;
        }
        
        // if the contact email doesn't have an @ symbol, continue
        if (!contacts[i]['email'].include('@')) {
            continue;
        }

        // check the form fields for a match with the contact email
        if (existsInArray(contactsInForm, contacts[i]['email'])) {
            continue;
        }

        // try adding to the first name/email field
        recipientsEmail = $('recipients_email');
        recipientsName = $('recipients_name');

        // set the values for the default inputs and move on to the next contact
        if (recipientsEmail.value.replace(/^\s+|\s+$/g, '').length == 0 || recipientsName.value.replace(/^\s+|\s+$/g, '').length == 0) {
            
            recipientsName.value = contacts[i]['name'];
            recipientsEmail.value = contacts[i]['email'];
            
            // if there was only 1 contact in the import, stop here
            if (contacts.length == 1) {
                stopImport();
            }
            
            continue;
        }

        // add to the existing name/email fields
        var li_mail = Element.extend(document.createElement("LI"));

        li_mail.addClassName('fields additional-row');
        
        // get row id which is empty (in between 0 to maxRecip-1)
        var rowId = 0;
        for (var j = 0; j < maxRecip; j++) {
                if ($('recipients_email'+j)) {
                        continue;
                }
                else{
                        rowId = j;
                        break;
                }
        }
        li_mail.innerHTML = '<p><a href="delete_email" title="Remove Email" onclick="remove_recipient('+rowId+'); return false" class="btn-remove">Remove Email<\/a><\/p>';
        li_mail.innerHTML += '<div class="field"><label for="recipients_name'+rowId+'" class="required"><em>*<\/em>Name:<\/label><div class="input-box"><input name="recipients[name][]" type="text" class="input-text required-entry" id="recipients_name'+rowId+'" value="' + contacts[i]['name'] + '" /><\/div>';
        li_mail.innerHTML += '<div class="field"><label for="recipients_email'+rowId+'" class="required"><em>*<\/em>Email Address:<\/label><div class="input-box"><input name="recipients[email][]" value="' + contacts[i]['email'] + '" title="Email Address" id="recipients_email'+rowId+'" type="text" class="input-text required-entry validate-email" /><\/div><\/div>';
        recipCount++;
        
        if (recipCount >= maxRecip && maxRecip != 0) {
            $('add_recipient_button').hide();
            $('max_recipient_message').show();
            disableConfirm();
        }

        ul.appendChild(li_mail);
    }
    setEmailAddressCheck();
    
    // do not hide once confirm the email list
    ///hideAddressRecords();
    
    $('recipients').scrollTo();
}

function filterAddressRecords (evt) {
	var i = 1;
    $('address_records').select('li.fields').each(function(s, index) {

        s.hide();

        var addressValues = s.select('span.address_record_name', 'span.address_record_email');

        if (addressValues[0].innerHTML.toLowerCase().include($F('address_search').toLowerCase()) 
            || addressValues[1].innerHTML.toLowerCase().include($F('address_search').toLowerCase())) {
            
            s.show();
			
			if (i++%2 != 0) {
				s.setStyle('background-color:#F4F3F3');
			}
			else {
				s.setStyle('background-color:#FAFAFA');
			}
        }
    });
    
    Event.stop(evt);
};

function ignoreEnterKey (evt) {
        if(evt.keyCode == Event.KEY_RETURN) {
                // stop processing the event, do nothing when pressing the enter key
                Event.stop(evt);
        }
}

function importAOL (evt) {
    var popUpUrl = baseUrl + '/cloudsponge/index/popup/service/aol';
    this.popUp = window.open(popUpUrl, 'import', 'width=987,height=600,resizable=yes,scrollbars=yes');
    this.popUp.focus();
    popupWinRef = this.popUp;
    $('import_loader').setStyle('display:inline');
    startImport();
    Event.stop(evt);
};

function importApple (evt) {
/*    var apple_iframe = $('apple-iframe');
    if (!apple_iframe) {
        ifrm = document.createElement("IFRAME");
        ifrm.setAttribute("id", "apple-iframe");
        ifrm.setAttribute("src", "/cloudsponge/index/popup/service/addressbook");
        ifrm.setAttribute("style", "position: absolute; top:100px; left:300px;");
        ifrm.style.width = 800+"px";
        ifrm.style.height = 600+"px";
        document.body.appendChild(ifrm);
    }
    else {
        apple_iframe.setStyle('display:block');
    }*/
   
    /*var apple_iframe = Element.extend(document.createElement("IFRAME"));
    apple_iframe.width = "500px";
    apple_iframe.height = "500px";
    apple_iframe.src = baseUrl + '/cloudsponge/index/popup/service/addressbook';

   document.body.appendChild(apple_iframe); */
    var popUpUrl = baseUrl + '/cloudsponge/index/popup/service/addressbook';
    this.popUp = window.open(popUpUrl, 'import', 'width=500,height=300,resizable=yes,scrollbars=yes');
    popupWinRef = this.popUp;
    window.focus();
    $('import_loader').setStyle('display:inline');
    startImport();
    Event.stop(evt);
};

function importGmail (evt) {
    if($('popup-content').hasClassName('gmail_imported')) {
        $('popup-overlay').show();
        $('popup-content').show();
        return;
    }
    imported = 'gmail_imported';
    var popUpUrl = baseUrl + '/cloudsponge/index/popup/service/gmail';
    this.popUp = window.open(popUpUrl, 'import', 'width=987,height=600,resizable=yes,scrollbars=yes');
    this.popUp.focus();
    popupWinRef = this.popUp;
    $('import_loader').setStyle('display:inline');
    startImport();
    Event.stop(evt);
};

function importMSN (evt) {
    if($('popup-content').hasClassName('msn_imported')) {
        $('popup-overlay').show();
        $('popup-content').show();
        return;
    }
     imported = 'msn_imported';
    var popUpUrl = baseUrl + '/cloudsponge/index/popup/service/windowslive';
    this.popUp = window.open(popUpUrl, 'import', 'width=987,height=600,resizable=yes,scrollbars=yes');
    this.popUp.focus();
    popupWinRef = this.popUp;
    $('import_loader').setStyle('display:inline');
    startImport();
    Event.stop(evt);
};
    
function importPlaxo (evt) {
    var popUpUrl = baseUrl + '/cloudsponge/index/popup/service/plaxo';
    this.popUp = window.open(popUpUrl, 'import', 'width=987,height=600,resizable=yes,scrollbars=yes');
    this.popUp.focus();
    popupWinRef = this.popUp;
    $('import_loader').setStyle('display:inline');
    startImport();
    Event.stop(evt);
};

function importUserPassword (evt) {
    var popUpUrl = baseUrl + '/cloudsponge/index/popup/service/' + inputService;
    popUpUrl += '/username/' + $('username').value + '/password/' + $('password').value;
    this.popUp = window.open(popUpUrl, 'import', 'width=600,height=987,resizable=yes,scrollbars=yes');
    this.popUp.focus();
    popupWinRef = this.popUp;
    $('import_loader').setStyle('display:inline');
    startImport();
    Event.stop(evt);
};

function importYahoo (evt) {
    if($('popup-content').hasClassName('yahoo_imported')) {
        $('popup-overlay').show();
        $('popup-content').show();
        return;
    }
     imported = 'yahoo_imported';
    var popUpUrl = baseUrl + '/cloudsponge/index/popup/service/yahoo';
    this.popUp = window.open(popUpUrl, 'import', 'width=500,height=500,resizable=yes,scrollbars=yes');
    this.popUp.focus();
    popupWinRef = this.popUp;
    $('import_loader').setStyle('display:inline');
    startImport();
    Event.stop(evt);
};

function setCounter(evt) {
    //alert(this.name + ' checked ' + this.checked);
    if (this.checked) {
        decreaseEmailCounter();
    }
    else {
        increaseEmailCounter();
    }
}

function selectAllAddressRecords (evt) {
    $$('input.address_record_checkbox_input').each(function(s, index) {
        if (counter>0 && !s.checked && $($(s.parentNode).parentNode).visible()) {
            // must set check status first:
            s.checked = true;
            decreaseEmailCounter();
        }
    });
    Event.stop(evt);
};

function selectNoneAddressRecords (evt) {
    $$('input.address_record_checkbox_input').each(function(s, index) {
        if (counter<maxRecip && s.checked) {
            increaseEmailCounter();
        }
        s.checked = false;
    });
    Event.stop(evt);
};

function showAOLUserPasswordForm (evt) {
    inputService = 'aol';
    $('username_label').innerHTML = '<em>*</em>AOL Username:';
    $('password_label').innerHTML = '<em>*</em>AOL Password:';
    stopImport();
    $('username').value = '';
    $('password').value = '';
    $('userpassword_inputs').show();
    Event.stop(evt);
};

function showPlaxoUserPasswordForm (evt) {
    inputService = 'plaxo';
    $('username_label').innerHTML = '<em>*</em>Plaxo Username:';
    $('password_label').innerHTML = '<em>*</em>Plaxo Password:';
    stopImport();
    $('username').value = '';
    $('password').value = '';
    $('userpassword_inputs').show();
    Event.stop(evt);
};

function hideAddressRecords() {
   
    $('cloudsponge_emailcounter').hide();
}

function showAddressRecords(address) {
    
    $('popup-content').show();
    $('cloudsponge_emailcounter').show();
    $('popup-overlay').show();
    $('address_records').show(); 
    if(address) {
        $('ul_email_add').show();
        $('select_emails').show();  
        return;
    }
    
     
    // set event loading the email list:
    $$('.address_record_checkbox_input').each(function(element){
        
        Event.observe(element, 'click', setCounter);
    });
    //counter = maxRecip; // do not reset counter 
   
    updateEmailCount();
}

function startImport() {
   
     
    importContactsFlag = true; 
    if (undefined != stopImportTimeCount) {
        clearTimeout(stopImportTimeCount);
    }
    
    if ($('cloudsponge_message')) {
        $('cloudsponge_message').setStyle('display:none');
    } 
    getJSONData();
    getErrorMessage();
    // set timeout handler
    setTimeout("importTimeout()", 180000);
}

function stopImport() {
    
    importContactsFlag = false;
    
    // stop the timer
    if (undefined != stopImportTimeCount) {
        clearTimeout(stopImportTimeCount);
    }
    
    $('import_loader').hide();

    if ($('apple-iframe')) {
        $('apple-iframe').setStyle('display:none');
    }

    // check if the popup window still exists; close popup window
    if (undefined != popupWinRef) {
       popupWinRef.close();
    }
}

// if there is no email addresses imported in 3 minutes, stop import procss
function importTimeout() {
    messageDiv = $('cloudsponge_message');
    if (messageDiv) {
        messageDiv.setStyle('display:inline');
        messageDiv.innerHTML = "<ul class=\"messages\"><li class=\"error-msg\"><ul><li>Process time out; please try again</li></ul></li></ul>";
    }
    stopImport();
}

function existsInArray(arrayOfValues, value) {
    
    for (i in arrayOfValues) {
        
        if (arrayOfValues[i] == value) {
            return true;
        }
    }
    
    return false;
}

function getJSONData() {
    
    // if the import contact flag is set to false, return
    if (!importContactsFlag) {
        return;
    }
   
    new Ajax.Request(baseUrl + '/cloudsponge/index/getcontactsjson/', {
        method: 'get',
        onSuccess: function(transport) {
            showAddressRecords(0);
            var contacts = '';
            if (transport.responseText) {
                contacts = transport.responseText.evalJSON();
            }
            else {
                // check if the popup window is still exist; stop request after 3 seconds if the popup window is closed
                if (undefined != popupWinRef && popupWinRef.closed) {
                   if (undefined == stopImportTimeCount) {
                        stopImportTimeCount = setTimeout("stopImport()", 60000);
                   }
                }
                getJSONData();
            }
            var i;
          
            // if the import contact flag is set to false, return
            if (!importContactsFlag) {
                return;
            }
            
            // if there were contacts in the AJAX resopnse, stop the import
            if (contacts.length) {
                stopImport();
            }
             
            ul = $('address_records');
            // (reset email list when import from a new resource, )
            ///ul.innerHTML = '';
            
            i = 1;
            var contactsInForm = new Array();
            //get existing email list
                
                $$('input.address_record_checkbox_input').each(function(s, index) {
                   s.ancestors()[1].descendants().each(function(s, index) {
                    if (s.hasClassName('address_record_email')) {
                        //alert(s.innerHTML);
                        contactsInForm.push(s.innerHTML);
                        i++;
                    }
                   });
                });

            for (idx in contacts) {

                // if the contact email doesn't have an @ symbol, continue
                if (!contacts[idx]['email'].include('@')) {
                    continue;
                }
               
                //showAddressRecords();
                 
                // check the email list for a match to skip duplicate emails
                if (existsInArray(contactsInForm, contacts[idx]['email'])) {
                    continue;
                }
                else {
                    
                    contactsInForm.push(contacts[idx]['email']);             
                }
                
                // add to the existing name/email fields
                var li_mail = Element.extend(document.createElement("LI"));
                li_mail.setStyle('padding: 8px');
                var contactName = (contacts[idx]['name'] == '') ? '&nbsp;' : contacts[idx]['name'];
                var contactEmail = (contacts[idx]['email'] == '') ? '&nbsp;' : contacts[idx]['email'];

                // set class for alternate Row background colors
                if (i%2 != 0) {
                    li_mail.setStyle('background-color:#F4F3F3');
                }
				else {
                    li_mail.setStyle('background-color:#FAFAFA');
				}
                li_mail.addClassName('fields');
                li_mail.innerHTML = '<div class="address_record_checkbox"><input type="checkbox" name="address_record[]" class="address_record_checkbox_input" value="' + contactEmail + '" id="address_record_checkbox' + i + '" /></div>';
                li_mail.innerHTML += '<div class="field_address_name"><span class="address_record_name" id="address_record_name' + i + '">' + contactName + '</span></div>';
                li_mail.innerHTML += '<div class="field_address_email">&lt;<span class="address_record_email" id="address_record_email' + i + '">' + contactEmail + '</span>&gt;</div>';
                li_mail.innerHTML += '</div>';
               
                i++;
                //recipCount++;
                
                if (recipCount >= maxRecip && maxRecip != 0) {
                    $('add_recipient_button').hide();
                    $('max_recipient_message').show();
                    disableConfirm();
                }

                ul.appendChild(li_mail);
                showAddressRecords(1);
            }
        }
    });
}

function getErrorMessage() {
    
    // if the import contact flag is set to false, return
    if (!importContactsFlag) {
        return;
    }

    new Ajax.Request(baseUrl + '/cloudsponge/index/geterrormessage/', {
        method: 'get',
        timeout: 10000,
        onSuccess: function(transport) {
            var message = transport.responseText;
            
            if (message.length) {
                // if the import contact flag is set to false, return
                if (!importContactsFlag) {
                    return;
                }
                stopImport();
                messageDiv = $('cloudsponge_message');
                if (messageDiv) {
                    messageDiv.setStyle('display:inline');
                    messageDiv.innerHTML = "<ul class=\"messages\"><li class=\"error-msg\"><ul><li>"+message+"</li></ul></li></ul>";
                }
            }
            else {
                // if the import contact flag is set to false, return
                if (!importContactsFlag) {
                    return;
                }
                getErrorMessage();
            }
        }
    });
}

