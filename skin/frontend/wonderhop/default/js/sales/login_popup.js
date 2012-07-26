function buildBlock(element) {
    jQuery.blockUI({ 
            message: jQuery(element), 
            // styles for the message when blocking; if you wish to disable 
            // these and use an external stylesheet then do this in your code: 
            // $.blockUI.defaults.css = {}; 
            css: { 
                padding:        '20px', 
                margin:         0, 
                width:          '30%', 
                top:            '30%', 
                left:           '35%', 
                textAlign:      'center', 
                color:          '#000', 
                border:         '3px solid #aaa', 
                backgroundColor:'#fff', 
                cursor:         'auto'
            }, 
            // styles for the overlay 
            overlayCSS:  { 
                backgroundColor: '#000', 
                opacity:         0.6 ,
                cursor:         'auto' 
            }, 
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
}); 
