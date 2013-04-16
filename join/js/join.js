function hcontact(el)
{
    var $ = jQuery;
    if ( ! $ || ! $(el)) return false;
    var href = $(el).attr('href');
    if ( ! href || (href.indexOf('contacting:') != 0)) return false;
    new_href = href.replace('contacting:','mailto:').replace('#','@').replace('/','.');
    $(el).attr('href', new_href);
    setTimeout(function(){ $(el).attr('href', href); }, 500);
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

function deleteCookie(name)
{
    setCookie(name, '' , -1);
}




function getTrack(url_param, cookie_param)
{
    var current = cookie_param ? getCookie(cookie_param) : null;
    
    if (current) return current;
    
    if (url_param)
    {
        
        if (window.location.search && window.location.search.indexOf(url_param + '=') > -1)
        {
            var params = window.location.search.replace(/((^\?)|(#.*$))+/g,'').split('&'), val = null;
            
            jQuery.each(params, function(i,p)
            {
                if (p.indexOf(url_param+'=') === 0)
                {
                    val = p.replace(url_param+'=','');
                    return false;
                }
            });
            
            if(val && cookie_param)
            {
                setCookie(cookie_param, val, 30);
            }
            
            return val;
        }
    }
    
    return null;
}


// local config object
window.Config || (window.Config = {});



// mixpanel lib
(function(c,a){window.mixpanel=a;var b,d,h,e;b=c.createElement("script");
b.type="text/javascript";b.async=!0;b.src=("https:"===c.location.protocol?"https:":"http:")+
'//cdn.mxpnl.com/libs/mixpanel-2.2.min.js';d=c.getElementsByTagName("script")[0];
d.parentNode.insertBefore(b,d);a._i=[];a.init=function(b,c,f){function d(a,b){
var c=b.split(".");2==c.length&&(a=a[c[0]],b=c[1]);a[b]=function(){a.push([b].concat(
Array.prototype.slice.call(arguments,0)))}}var g=a;"undefined"!==typeof f?g=a[f]=[]:
f="mixpanel";g.people=g.people||[];h=['disable','track','track_pageview','track_links',
'track_forms','register','register_once','unregister','identify','alias','name_tag',
'set_config','people.set','people.increment'];for(e=0;e<h.length;e++)d(g,h[e]);
a._i.push([b,c,f])};a.__SV=1.2;})(document,window.mixpanel||[]);

(function(config){ if (config)
{
    mixpanel.init(config.key, config.options || {});
    
}})((Config.mixpanel && Config.mixpanel.key && Config.mixpanel) || null);

// track data object
window.Track || (window.Track = {});


// gather track data
(function()
{
    Track.page =
    {
        ad_code : getTrack('a', 'curio_ad_code'),
    };
})();
