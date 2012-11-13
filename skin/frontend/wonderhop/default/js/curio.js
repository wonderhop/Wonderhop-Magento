 function clear_gift_message(cond)
{
    var $ = jQuery, $items = $('input,textarea','#allow-gift-message-container');
    $items.length && ((cond && $items.attr('disabled',false)) || $items.attr('disabled','disabled'));
}
