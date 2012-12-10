var jSharer = (function(){
    var $ = jQuery, $this , $sharer = function jSharer(elem, sharer, options){ return $this.bind(elem, sharer, options); };
    $this = {
        options : {
            win_w       : 575,
            win_h       : 520,
            win_tx      : 'Share This',
            win_l       : ($(window).width()  - 575)  / 2,
            win_t       : ($(window).height() - 520) / 2,
            text        : 'Share This',
            sharer      : '#',
            url         : '#',
            media       : '',
            params      : {}
        },
        
        sharer_options : {
            'pinterest' : {
                sharer : 'http://pinterest.com/pin/create/button/',
                win_tx : 'Pin This !',
                params : {'url':'url','media':'media','description':'text'}
            },
            'twitter' : {
                sharer : 'http://twitter.com/share/',
                win_tx : 'Tweet This !',
                params : {'url':'url','text':'text'}
            },
            'facebook' : {
                sharer : 'http://www.facebook.com/sharer.php',
                win_tx : 'Post on Wall',
                params : {'u':'url','t':'text'}
            }
        },
        
        config : {
            errors : window.console,
            mu_attr : 'jshare_data'
        },
        
        sharers : ['pinterest', 'twitter', 'facebook'],
        
        beforeBindAll : null,
        afterBindAll : null,
        
        $front : $sharer,
        
        configure : function(config)
        {
            if (typeof config != 'object') return $this;
            var config = $.extend({}, config);
            if (config.options) {
                if (typeof config.options == 'object') {
                    $.extend($this.options, config.options);
                }
                delete config['options'];
            }
            $.extend($this.config, config);
            return $this.$front;
        },
        
        $bind : function(share, opts)
        {
            $(this).each(function(i,e){
                $this.bind(e, share, opts);
            });
            return $(this);
        },
        
        bind : function(elem, sharer, options) {
            var $elem = $(elem).first();
            if ( ! $elem.length) {
                $this.dispatchError('Supplied element not found in DOM', elem);
                return $elem;
            }
            if ( ! $this.isSupported(sharer)) {
                $this.dispatchError('Sharer not supported', sharer);
                return $elem;
            }
            var options = $this.getOptions($elem, sharer, options);
            $this.makeShare($elem, options);
            return $elem;
        },
        
        getOptions : function($e, sharer, options)
        {
            return $.extend({}, 
                $this.options, 
                $this.sharer_options[sharer], 
                $this.getMarkupOptions($e), 
                $this.objFilter(options)
            );
        },
        
        getMarkupOptions : function($e)
        {
            var optsStr = $e.attr($this.config.mu_attr);
            var muopts = optsStr ? optsStr.split(',') : null, opts = {};
            if (muopts) {
                $.each(muopts, function(i,opt){
                    if(opt.indexOf('=') > -1) {
                        var kv = opt.split('=');
                        opts[kv[0]] = kv[1];
                    }
                });
            }
            if ( ! muopts || ! opts.url || opts.url == '#'){
                var $is_a = ($e.get()[0].tagName.toLowerCase() == 'a') ? $e : null;
                var $l = $is_a ? $is_a.add( $e.find('a') ) : $e.find('a');
                if (( ! opts.url || opts.url=='#') && $l && $l.length) {
                    var _url = '#';
                    $l.each(function(i,e){
                        if ($(e).attr('href') != '#') {
                            _url = $(e).attr('href');
                            return false;
                        }
                    });
                    opts.url = _url;
                }
            }
            opts.url = $this.encodeUrl(opts.url);
            return opts;
        },
        
        objFilter : function(options)
        {
            return (typeof options != 'object') ? null : options;
        },
        
        dispatchError : function(error, prop)
        {
            if ($this.config.errors) {
                if (typeof $this.config.errors == 'object' && typeof $this.config.errors.log == 'function') {
                    $this.config.errors.log('jSharer: ' + error , prop);
                }
                throw new Error(error);
            }
        },
        
        $shareIt : function(ev)
        {
            var $e = jQuery(this), $share = $e.data('jSharer');
            if (!!$share) {
                ev.preventDefault();
                ev.stopPropagation();
                args = [$share.sharer_url, $share.title, $share.options];
                if (typeof $share.callback == 'function') {
                    if ($share.callback.apply(this, [ev].concat(args)) === false) return $e;
                }
                window.open.apply(window, args);
            }
            return false;
        },
        
        
        makeShare : function($el,opts)
        {
            var $a = $el.find('a').first();
            //if ( ! opts.url || opts.url == '#') opts.url = $el.find('a').first().attr('href');
            var sharer = opts.sharer + '?',
                win_opts = 'status=1,width='+ opts.win_w +',height='+ opts.win_h +',top='+ opts.win_t +',left='+ opts.win_l;
            $.each(opts.params, function(urlkey,key) { 
                if(opts[key]) {
                    sharer += urlkey + '=' + opts[key] + '&';
                }
            });
            sharer = sharer.slice(0,sharer.length-1);
            var $share = { 'sharer_url' : sharer, 'title' : opts.win_tx, 'options' : win_opts },
                rebind = !!$el.data('jSharer');
            $el.data('jSharer', $share);
            if ( ! rebind) {
                $el.on('click', $this.$shareIt);
            }
        },
        
        encodeUrl : function(url)
        {
            return encodeURIComponent(url);
        },
        
        isSupported : function(share)
        {
            return ($this.sharers.indexOf(share) > -1);
        },
        
        $addCallback : function(callback)
        {
            $(this).each(function(i,e) {
                $this.addCallback(e, callback);
            });
            return $(this);
        },
        
        addCallback : function(elem, callback)
        {
            var $elem = $(elem).first();
            if ( ! $elem.length || ! $elem.data('jSharer')) return $this;
            $elem.data('jSharer').callback = callback;
            return $this.$front;
        },
        
        
        bindAll : function(config, before, after)
        {
            if (config) $this.configure(config);
            if (typeof $this.beforeBindAll == 'function') $this.beforeBindAll.call(null);
            $('.jshare').each(function(i,e){
                var share = $(e).attr('class').replace(/^.*?jshare\-/,'').replace(/\s.*$/,'');
                if ($this.isSupported(share)) $(e).jSharer(share);
            });
            if (typeof $this.afterBindAll == 'function') $this.afterBindAll.call(null);
            return $this.$front;
        },
        
        bindAllOnDocReady : function(config)
        {
            $(function(){ $this.bindAll(config); });
            return $this.$front;
        },
        
        getBinded : function()
        {
            var $all = $([]);
            $('.jshare').each(function(i,e){
                if ($(e).data('jSharer')) $all = $all.add(e);
            });
            return $all;
        },
        
        setBindAllCallbacks : function(before, after)
        {
            $this.beforeBindAll = before;
            $this.afterBindAll = after;
            return $this.$front;
        }
        
    }
    
    $sharer.bindShare           = $this.bind;
    $sharer.configure           = $this.configure;
    $sharer.supports            = $this.isSupported;
    $sharer.bindAllOnDocReady   = $this.bindAllOnDocReady;
    $sharer.bindAll             = $this.bindAll;
    $sharer.getBinded           = $this.getBinded;
    $sharer.setBindAllCallbacks = $this.setBindAllCallbacks;
    
    
    $.fn.jsharer            = $this.$bind;
    $.fn.jSharer            = $this.$bind;
    $.fn.jShareCallback     = $this.$addCallback;
    
    
    return $sharer;
})();
