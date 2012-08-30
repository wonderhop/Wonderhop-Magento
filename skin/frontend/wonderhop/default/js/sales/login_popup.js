function buildBlock(element) {
    jQuery.blockUI({ 
            message: jQuery(element), 
            // styles for the message when blocking; if you wish to disable 
            // these and use an external stylesheet then do this in your code: 
            // $.blockUI.defaults.css = {}; 
            css: { 
                padding:        '227px 131px 46px 96px', 
                margin:         0, 
                width:          563, 
                height:         237,
                position:       'fixed',
                marginTop:      '50%',
                top:            -270,
                right:          0,
                left:           'auto',
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
