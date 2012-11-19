GiftShop = (function($){ return {
    data : {},
    _init : false,
    
    init : function(data, container)
    {
        this.data.tags = data.tags;
        this.data.labels = data.labels;
        this.data.prices = data.prices;
        if ( ! data.ranges['price-single-gteq-0']) data.ranges['price-single-gteq-0'] = 'All';
        this.data.ranges = data.ranges;
        this.data.filters = {};
        this.data.$container = this.getContainer();
        if ( ! this.data.$container.length) return;
        this.buildFilters();
        this.scheduleLoadProducts();
        this.bindFilters();
        this._init = true;
        return this;
    },
    
    getContainer : function()
    {
        return $('#filters-box');
    },
    
    getColumns : function()
    {
        return $('ul.prod-col');
    },
    
    loadProducts : function()
    {
        this.data.products = $('.category-products li.item');
    },
    
    scheduleLoadProducts : function()
    {
        $( this.loadProducts.bind(this) );
    },
    
    reFlowProducts : function()
    {
        var $columns = this.getColumns();
        var products = $(this.data.products).filter(':visible').get();
        current = -1;
        while(products.length) {
            current++;
            if(current >= $columns.length) { current = -1; continue; }
            $($columns[current]).append( products.shift() );
        };
    },
    
    buildFilters : function()
    {   
        this.data.filters.$tags = $('<select id="filter-tags" class="gs-filter"></select>');
        this.data.filters.$price = $('<select id="filter-price" class="gs-filter"></select>');
        $.each(this.data.filters, function(k,e){ 
            var $wrap = $('<div class="input-box gs-filter-wrap"></div>');
            var name = (k == '$tags') ? 'For : ' : 'Price range : ';
            var $label = $('<label for="filter-'+k.slice(1)+'">'+name+'</label>');
            $wrap.append(e);
            this.data.$container.append($label).append($wrap); 
        }.bind(this));
        // "everyone" tag must be the first option
        this.data.filters.$tags.append( this.makeOption('Everyone', 'filter-filter-tag','filter-filter-tag') );
        $.each(this.data.tags, function(className, count){
            var $e = this.makeOption(this.data.labels[className], 'filter-'+className,'filter-'+className);
            this.data.filters.$tags.append( $e );
        }.bind(this));
        // prices need to be in order
        var _opts = {}, _iopts = [];
        $.each(this.data.ranges, function(className, label){
            var $e = this.makeOption(label, 'filter-'+className,'filter-'+className);
            var val = parseInt(className.split('-').pop());
            _opts[val] = $e;
            _iopts.push(val);
        }.bind(this));
        _iopts.sort();
        $.each(_iopts, function(i,e){
            this.data.filters.$price.append( _opts[e] );
        }.bind(this));
    },
    
    
    bindFilters : function()
    {
        var GS = this; $('#filter-tags, #filter-price').change(function(ev){ GS.applyFilters(); });
    },
    
    
    getFilter : function(type)
    {
        var f = 'filter' + type.charAt(0).toUpperCase() + type.toLowerCase().slice(1);
        return typeof this[f] == 'function' ? this[f]() : '';
    },
    
    filterTags : function()
    {
        // get selected tag filter
        return '.' + this.data.filters.$tags.find(':selected').attr('id').replace(/^filter\-/,'');
    },
    
    filterPrice : function()
    {
        // get selected price range
        var pRange = this.data.filters.$price.find(':selected').attr('id').replace(/^filter\-price\-/,''),
            single = /^single-/i, double = /^double-/i, pc = [];
        if (single.test(pRange)) {
            var r = pRange.replace(single,'').split('-'), op = r.shift(), val = r.pop(),
                pc = this.getFilteredPrices(op, val);
        } else if ( double.test(pRange) ) {
            var r = pRange.replace(double,'').split('-'), op1 = r.shift(), val1 = r.shift(), op2 = r.shift(), val2 = r.shift(),
                pc = this.getFilteredPrices(op2, val2,  this.getFilteredPrices(op1, val1) );
        }
        var classes = [];
        $.each(pc, function(i,e){ classes.push('.price-'+e); });
        return classes.join(' ,');
    },
    
    getFilteredPrices : function(op, val, inject)
    {
        var prices = inject && inject instanceof Array ? inject : this.data.prices, filtered = [];
        $.each(prices, function(i,p){
            switch(op) {
                case 'lt':
                    if (p < val) filtered.push(p); break;
                case 'lteq' : 
                    if (p <= val) filtered.push(p); break;
                case 'gt':
                    if (p > val) filtered.push(p); break;
                case 'gteq' : 
                    if (p >= val) filtered.push(p); break;
            }
        });
        return filtered;
    },
    
    
    applyFilters : function(filter)
    {
        // get all checked filters
        /*var _filters = [];
        $('.filterbox').each(function(i,e){
            var _filter = '.' + $(e).attr('id').replace(/^filter-/, '');
            if ($(e).is(':checked')) _filters.push(_filter);
        }.bind(this));*/
        var filters = ['tags', 'price'], _filters = [];
        $.each(filters, function(i,f){
            _filters.push( this.getFilter(f) );
        }.bind(this));
        this.applyFilter( _filters , true );
        this.reFlowProducts();
    },
    
    applyFilter : function(filter, direction)
    {
        var selection = this.getSelection(filter[0]),
            action = direction ? 'show' : 'hide',
            unAction = direction ? 'hide' : 'show';
        if(filter.length > 1) {
            for (var i=1; i < filter.length; i++) {
                selection = this.getSelection(filter[i], selection.positive);
            }
        }
        selection.positive[action]();
        selection.negative[unAction]();
    },
    
    getSelection : function(filter, objects)
    {
        var objects = objects ? objects : $(this.data.products),
            all = $(this.data.products),
            _in = objects.filter(filter),
            _out = all.not(_in);
        return {positive: _in, negative: _out };
    },
    
    
    
    makeCheckbox : function(label, name, classes, id, value, checked)
    {
        var $div = $('<div class="checkbox"></div>'),
        $e = $('<input type="checkbox" class="'+classes+'" name="'+name+'" id="'+id+'"'
                +' value="'+(typeof value != 'undefined' ? 1 : value)+'"'
                +(!!checked ? ' checked="checked"' : '')
                + '/>');
        $l = $('<label for="'+name+'">'+label+'</label>');
        $div.append($e).append($l);
        return $div;
    },
    
    makeOption : function(label, id, value)
    {
        $e = $('<option id="'+id+'"'+' value="'+value+'"'+'>'+label+'</option>');
        return $e;
    },
    
}})(jQuery); 
