var GenShare = (function($){ function GenShare(){ var self = this; var _ ={}; return $.extend(self,{
    $w : $(window),
    init: function()
    {
        _.def_opts    = {
            win_w       : 575,
            win_h       : 400,
            win_tx      : 'Share This',
            win_l       : ($(window).width()  - 575)  / 2,
            win_t       : ($(window).height() - 400) / 2,
            text        : 'Share This',
            sharer      : '#',
            url         : '#',
            media       : '',
            params      : {},
        };
        _.gen_window = function(el,opts)
        {
            var $el = $(el).first(),
                $a = $('a',$el).first();
            if ( ! $el || ! $el.length || ! $a || ! $a.length) return false;
            if ( ! opts.url || opts.url == '#') opts.url = $a.attr('href');
            var sharer = opts.sharer + '?',
                win_opts = 'status=1,width='+ opts.win_w +',height='+ opts.win_h +',top='+ opts.win_t +',left='+ opts.win_l;
            $.each(opts.params, function(urlkey,key) { 
                if(opts[key]) {
                    sharer += urlkey + '=' + opts[key] + '&';
                }
            });
            sharer = sharer.slice(0,sharer.length-1);
            window.open(sharer, opts.win_tx, win_opts);
            return false;
        }
        return self;
    },
    
    post_on_wall : function(el,opts)
    {
        var mid_opts = $.extend({},_.def_opts,{
                sharer : 'http://www.facebook.com/sharer.php',
                win_tx : 'Post on Wall',
                params : {'u':'url','t':'text'},
            }),
        opts = (typeof opts == 'object') ? $.extend({},mid_opts,opts) : $.extend({},mid_opts);
        return _.gen_window($(el),opts);
    },
    
    tweet_it : function(el,opts)
    {
        var mid_opts = $.extend({},_.def_opts,{
                sharer : 'http://twitter.com/share/',
                win_tx : 'Tweet This !',
                params : {'url':'url','text':'text'},
            }),
        opts = (typeof opts == 'object') ? $.extend({},mid_opts,opts) : $.extend({},mid_opts);
        return _.gen_window($(el),opts);
    },
    
    pin_it : function(el,opts)
    {
        var mid_opts = $.extend({},_.def_opts,{
                sharer : 'http://pinterest.com/pin/create/button/',
                win_tx : 'Pin This !',
                params : {'url':'url','media':'media','description':'text'},
            }),
        opts = (typeof opts == 'object') ? $.extend({},mid_opts,opts) : $.extend({},mid_opts);
        return _.gen_window($(el),opts);
    },
    
}).init();}; return new GenShare();})(jQuery);
