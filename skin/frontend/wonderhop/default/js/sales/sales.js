var variables_to_set = ['r', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'confirmation', 'a'];
 
/* handle referer url */
if (! getCookie('wonderhop_referer_url') && document.referrer != '')
    setCookie('wonderhop_referer_url', document.referrer, 30);
var matches  =  (document.referrer.match(/^https?:\/\/(?:[^\/]+\.)?([^.\/]+\.[a-z]+)(?:\/|$)/));
var referer_domain = "";
if (matches) referer_domain = matches[1];

var referral_id; 

for (i=0; i <  variables_to_set.length; i++) {
    setParamCookie(variables_to_set[i]);
}


function setParamCookie(cookie_name) {

    if (getCookie('wonderhop_' + cookie_name)) {
        return;
    }
    
    var re = new RegExp('[\?\&]?' + cookie_name + '=');
    var value = '';
    if (document.location.href.match(re)) {
        var re_value = new RegExp( '[\?\&]?' +  cookie_name + '=([^\&]+)');
        value = document.location.href.match(re_value)[1]; 
        value = value.replace('/', '');
        
    }
    
    if (value && cookie_name == 'r') referral_id = value;

    if (!value && cookie_name == 'utm_source') {
        if (referer_domain == '' && referral_id == null ) value = 'direct';
        if (referer_domain == '' && referral_id != null ) value = 'referral non-web';
        if (referer_domain != '' && referral_id == null ) value = 'organic '+ referer_domain;
        if (referer_domain != '' && referral_id != null ) value = 'referral '+ referer_domain;
    } 
    
    if (value) {
        setCookie('wonderhop_' + cookie_name, value, 30);
    } 
     
}

function setCookie(c_name,value,exdays)
{
    var exdate=new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
    document.cookie=c_name + "=" + c_value+"; path=/";
} 

function getCookie(c_name)
{
    var i,x,y,ARRcookies=document.cookie.split(";");
    for (i=0;i<ARRcookies.length;i++)
    {
      x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
      y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
      x=x.replace(/^\s+|\s+$/g,"");
      if (x==c_name)
        {
        return unescape(y);
        }
      }
}

(function ($) {

    $.fn.getWidthInPercent = function () {
        var width = parseFloat($(this).css('width'))/parseFloat($(this).parent().css('width'));
        return Math.round(100*width)+'%';
    };

})(jQuery);


(function($){
    $.fn.extend({
    customStyle : function(options) {
        if(!$.browser.msie || ($.browser.msie&&$.browser.version>6)) {
            return this.each(function() {
                //console.log(this);
                var currentSelected = $(this).find(':selected');
                var $wrap = $('<div class="customStyleWrap" style="position:relative;margin-right:14px;"></div>');
                $(this).after($wrap);
                $(this).appendTo($wrap);
                $(this).after( '<span class="customStyleSelectBox">'
                                        +currentSelected.text()
                                    +'</span>'
                                +'<span class="customStyleSelectBoxInner"></span>'
                        ).css({opacity:0, fontSize: $(this).next().css('font-size')});
                //$('.customStyleWrap').each(function(i,e){
                //    var $e = $(e);
                //    $e.css({width: $e.prev().width() });
                //    console.log($e);
                //    console.log($e.prev());
                //    console.log($e.prev().width());
                //});
                var selectBoxSpan = $(this).next();
                var selectBoxWidth = $(this).css('width');  
                var selectBoxSpanInner = selectBoxSpan.find(':first-child');
                selectBoxSpan.css({display:'block'});
                $wrap.css({width:$(this).getWidthInPercent()});
                $(this).css('width','100%');
                //$('.customStyleSelectBox').css({width:selectBoxWidth, display:'block'});
                //var selectBoxHeight = 31;
                $(this).change(function() {
                     $(this).next('.customStyleSelectBox').text($(this).find(':selected').text());
                });
                $(this).addClass('customStyle');
         });
        }
    }
    });
})(jQuery);


/*
function doCustomStyle() {
    var elems =jQuery('select').not(jQuery('.payment-methods select'));
    console.log(jQuery.customStyle);
    if (jQuery.fn.customStyle) {
        elems.customStyle();
    }
    
    customStyleEngage();
}

function customStyleEngage() {
    //setTimeout(doCustomStyle, 1000);
}

jQuery( doCustomStyle );
*/

//jQuery(function(){ jQuery('select').not('.customStyle').customStyle(); });

function getCustomStyleables(include,exclude) {
    var o = {}, $ = jQuery, $elems = jQuery('select').not('.customStyle');
    if( ! $elems.length) {
        if ( ! include) return o;
        return $(include);
    } else {
        if (include) $elems.add($(include));
        if (exclude) $elems.not($(exclude));
        return $elems;
    }
}

function doCustomStyle(include, exclude) {
    getCustomStyleables(include,exclude).customStyle ? getCustomStyleables().customStyle() : 0;
    customStyleEngage(include,exclude);
    //console.log('dcs');
}

function customStyleEngage(inc,exc) {
    setTimeout(function(){ doCustomStyle(inc,exc) },500);
}

doCustomStyle(false,'.limiter select');
