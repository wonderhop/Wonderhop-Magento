//
function clear_gift_message(cond)
{
    var $ = jQuery, $items = $('input,textarea','#allow-gift-message-container');
    $items.length && ((cond && $items.attr('disabled',false)) || $items.attr('disabled','disabled'));
}


// bind all sharers on docready and init mixpanel events for them 
jSharer.setBindAllCallbacks(null, function(){
    jSharer.getBinded().jShareCallback(function(){
        if (jQuery(this).attr('mp_event')) { mixpanel.track(jQuery(this).attr('mp_event')); };
    });
}).bindAllOnDocReady();



// transforms mailto: links dynamically
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

if(typeof Validation != 'undefined') Validation.get('validate-email').error = 'Please enter a valid email address.';
