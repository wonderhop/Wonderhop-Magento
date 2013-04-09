function buildBlock(element) {
    if ( window.canClosePopup && ! jQuery(element).find('.unblockui').length)
    {
        jQuery(element).append('<a class="unblockui" href="#">X</a>');
        if ( ! jQuery('body').data('bound_close_login_popup_on_exit'))
        {
            jQuery('body').data('bound_close_login_popup_on_exit', true);
            jQuery('body').keyup(function(ev)
            {
                var ESC = 27, key = ev.which || ev.keyCode;
                if (key == ESC)
                {
                    hideLoginPopup();
                }
            });
        }
    }
    jQuery.blockUI({ 
            message: jQuery(element), 
            // styles for the message when blocking; if you wish to disable 
            // these and use an external stylesheet then do this in your code: 
            // $.blockUI.defaults.css = {}; 
            /*css: { 
                padding:        0, 
                margin:         0, 
                width:          '100%', 
                height:         '100%',
                position:       'absolute',
                top:            0,
                bottom:         0,
                right:          0,
                left:           0,
                textAlign:      'center', 
                color:          '#000', 
                border:         'none', 
                backgroundColor:'transparent',
                cursor:         'auto',
            }, */
            // styles for the overlay 
            /*overlayCSS:  { 
                backgroundColor: '#000', 
                opacity:         0.6 ,
                cursor:         'auto' 
            },*/ 
            showOverlay: false
        });
        /*jQuery('html').css({height:'100%',overflow:'hidden'});*/
        jQuery('.unblockui').not('.bound').addClass('bound').click(function(ev)
        {
            ev.preventDefault();
            hideLoginPopup();
        });
        setTimeout(function(){ jQuery(element).find('input[type="text"],input[type="email"]').blur(); },50);
}
jQuery(document).ready(function() {
    if ( ! window.isCollection && ! window.isCart && ! window.isCheckout)
    {
        jQuery('.login_overlay').show();
        
        if (jQuery('.popup_register').length) {
            buildBlock('.popup_register'); 
        } else if(jQuery('.popup_register_2').length) {
            buildBlock('.popup_register_2');
        } else if(jQuery('.popup_login').length) {
            buildBlock('.popup_login');
        } else {
        }
    }
    else
    {   
        if ( ! window.isLoggedIn)
        {
            jQuery('.login_overlay').hide();
            window.canClosePopup = true;
            jQuery('.welcome-box').html('LOGIN').addClass('popup-trigger').click(function(ev)
            {
                ev.preventDefault();
                showLoginPopup();
            });
            jQuery('#back_to_register').length && jQuery('#back_to_register').html('Sign Up').css({width:105});
        }
    }
    
     jQuery('#login_link').click(function(){
          
         buildBlock('.popup_login'); 
     }); 
     
     jQuery('#forgot_link').click(function(){
         buildBlock('.forgot_form'); 
          return false;
     }); 
     
     jQuery('#back_to_login').click(function(){
         buildBlock('.popup_login'); 
         return false;
     }); 
     jQuery('#back_to_register').click(function(){
          buildBlock('.popup_register'); 
         return false;
     });
}); 


var showLoginPopup = function()
{
    jQuery('.login_overlay').show();
    buildBlock('.popup_login');
}

var hideLoginPopup = function()
{
    jQuery.unblockUI();
    jQuery('.login_overlay').hide();
}
