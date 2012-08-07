var variables_to_set = ['r', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
 
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
