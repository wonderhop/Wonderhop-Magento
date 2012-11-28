 var dynHead = (function($){
    var $win = $(window),
        $t = $('.tag_header').first(),
        wr = $('.tag_header_wire')[0].outerHTML,
        $c = $('<div></div>').insertBefore($t),
        is_gs = (window['GiftShop'] && GiftShop._init),
        $f = null,
        $fp = is_gs ? GiftShop.data.$container.parent() : null,
        $f = is_gs ? GiftShop.data.$container.detach() : null,
        $tc = $t.clone().html(wr).appendTo($c).css({display:'none', zIndex:1000}).addClass('clone'),
        $h = $('.dynamic_header').first(),
        $hc = $h.clone().appendTo($c).css({display:'none',zIndex:1010}).addClass('clone'),
        _limit1 = $h.offset().top + 30,
        _limit2 = $t.offset().top+$t.height() - $h.height();
    
    $hc.css({position:'fixed', left:'50%', marginLeft: '-'+($h.width()/2)+'px' , top:-7});
    $tc.css({position:'fixed', left:'50%', marginLeft: '-'+($t.width()/2 + 70 - 15)+'px' , top: '-'+($t.height()-$h.height() +20)+'px' });
    $fix = $('<div></div>').css({display:'none', position:'fixed', width:$hc.width(), height: 10, top:0, left:'50%', 
        marginLeft: '-'+($h.width()/2-12)+'px', background:'white', zIndex:1005});
    $fix.appendTo($c);
    
    if (is_gs) {
        $fp.append($f);
    }
    
    var moveFiltersTo = function($el)
    {
        if ( ! is_gs ) return;
        $el.append($f);
    }
    
    var stick1 = function() {
        $h.css({visibility:'hidden'});
        $hc.css({display:'block'});
        $fix.css({display:'block'});
        moveFiltersTo($hc);
    }
    var unstick1 = function() {
        $h.css({visibility:'visible'});
        $hc.css({display:'none'});
        $fix.css({display:'none'});
        moveFiltersTo($h);
    }    
    var stick2 = function() {
        $t.css({visibility:'hidden'});
        $tc.css({display:'block'});
    }
    var unstick2 = function() {
        $t.css({visibility:'visible'});
        $tc.css({display:'none'});
    }
    $win.scroll(function(ev){
        if ($win[0].scrollY > _limit1){
            stick1();
        } else {
            unstick1();
        }
        if ($win[0].scrollY > _limit2){
            stick2();
        } else {
            unstick2();
        }
    });
    
})(jQuery);
