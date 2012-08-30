function buildBlock(element) {
    jQuery.blockUI({ 
            message: jQuery(element), 
            // styles for the message when blocking; if you wish to disable 
            // these and use an external stylesheet then do this in your code: 
            // $.blockUI.defaults.css = {}; 
            css: { 
                padding:        0, 
                margin:         0, 
                width:          '100%', 
                height:         '100%',
                position:       'fixed',
                top:            0,
                bottom:         0,
                right:          0,
                left:           0,
                textAlign:      'center', 
                color:          '#000', 
                border:         'none', 
                backgroundColor:'transparent',
                cursor:         'auto',
            }, 
            // styles for the overlay 
            overlayCSS:  { 
                backgroundColor: '#000', 
                opacity:         0.6 ,
                cursor:         'auto' 
            }, 
            showOverlay: false,
        });
}
jQuery(document).ready(function() { 
     if (jQuery('.popup_register').length) {
        buildBlock('.popup_register'); 
     } else {
        buildBlock('.popup_register_2');
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
