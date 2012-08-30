/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    OSL
 */
/*
var loadingW = 260;
var loadingH = 50;
var confirmW = 260;
var confirmH = 134;
*/
var inCart = false;
var saved_wrapper_w=0;
var saved_wrapper_h=0;
var isLoading = false;
var j2t_error = "";

var waitProductDetails = false;

if (window.location.toString().search('/product_compare/') != -1){
	var win = window.opener;
}
else{
	var win = window;
}

if (window.location.toString().search('/checkout/cart/') != -1){
    inCart = true;
}

function setLocationJ2T(url){
    //alert(url);
    //return_url
    var myAjax = new Ajax.Request(
    'http://127.0.0.1/magento_tests_1_6_1_0/index.php/checkout/cart/',
    {
        asynchronous: true,
        method: 'post',
        postBody: '',
        onException: function (xhr, e)
        {
            alert('Exception : ' + e);
        },
        onComplete: function (xhr)
        {
            //var return_value = xhr.responseText;
            /*if (return_value != 0){
                //alert(return_value);
                sendoptions(return_value);
            } else {
                sendQtyAsk(url);
            } */  
            document.location.href = url;
        }
    });
}

function setLocation(url){
    if(!inCart && (/*(url.search('/add') != -1 ) || (url.search('/remove') != -1 ) ||*/ url.search('checkout/cart/add') != -1) ){
        showJ2tProductDetails(url);
        if (ajax_cart_qty == 1){
            showLoading();
	    var j2tReg = /\/product\/([0-9]+)/;
            if (url.search(j2tReg) != -1) {
                //check if product has options
                //get product id
                var j2tMatches = url.match(j2tReg);
                if (j2tMatches[1] != undefined){
                    //alert(j2tMatches[1]);
                    var url_product_check =  j2tajaxcart_url_check.replace('product_id', j2tMatches[1]);
                    checkProductUrlJ2tQty(url_product_check, url);
                }
            } else {
                //j2tSendCartUrl(url, qty_to_insert);
                sendQtyAsk(url);
            }
            //sendQtyAsk(url);
        } else {
            sendcart(url, 'url', 1, '');
        }
    } else if (!inCart && url.search('options=cart') != -1) {
        showJ2tProductDetails(url);
        sendoptions(url);
    } else{
        //window.location.href = url;
        document.location.href = url;
        //parent.location.href = url;
    }
}



document.observe("dom:loaded", function() {
    if (optionsPrice == undefined){
        var optionsPrice;
    }
    if (productAddToCartForm == undefined){
        var productAddToCartForm;
    }
    if (optionsPrice == undefined){
        var optionsPrice;
    }
    if (spConfig == undefined){
        var spConfig;
    }
    if (DateOption == undefined){
        var DateOption;
    }
});


function getQtyValue(){
    var qty_val = $('j2t_ajax_confirm_wrapper').down('.qty');
    if (isNaN(qty_val.value)){
        return 1
    } else {
        return qty_val.value;
    }
}

function sendQtyAsk(url){
    showLoading();
    $('j2t_ajax_qty').down('.j2t-btn-cart').stopObserving();
    $('j2t_ajax_qty').down('.j2t-btn-cart').observe('click', function(){
                                                            sendcart(url, 'url', getQtyValue(), '');
                                                        });
    var productDetailsInterval = setInterval(function(){
        if (!waitProductDetails){
            var j2t_prod_details = '';
            if (j2t_ajax_cart_show_details == 1){
                j2t_prod_details = '<div class="j2t-product-details">'+$('j2t-product-details').innerHTML+'</div>';
            }
            var qty_content = j2t_prod_details+$('j2t_ajax_qty').innerHTML;

            $('j2t_ajax_confirm').update('<div id="j2t_ajax_confirm_wrapper">'+qty_content+ '</div>');
            showConfirm();

            $('j2t_ajax_confirm').down('.j2t-btn-cart').stopObserving();
            $('j2t_ajax_confirm').down('.j2t-btn-cart').observe('click', function(){
                                                                        sendcart(url, 'url', getQtyValue(), '');
                                                                    });
            $('j2t_ajax_confirm').down('.inner-ajax-content').insert({bottom: new Element('div', {id: 'j2t-bottom-qty'})});
            $('j2t-bottom-qty').innerHTML = "&nbsp;";
            clearInterval(productDetailsInterval);
        } 
    },500);
}


function sendoptions(url){
    if (j2t_show_options == 0){
        document.location.href = url;
    } else {
        showLoading();
        url = url.replace('checkout/cart', 'j2tajaxcheckout/index/cart/cart');
        
        var productDetailsIntervalOptions = setInterval(function(){
            if (!waitProductDetails){
                var j2t_prod_details = '';
                if (j2t_ajax_cart_show_details == 1){
                    j2t_prod_details = '<div class="j2t-product-details">'+$('j2t-product-details').innerHTML+'</div>';
                }
                
                var myAjax = new Ajax.Request(
                url,
                {
                    asynchronous: true,
                    method: 'post',
                    postBody: '',
                    onException: function (xhr, e)
                    {
                        alert('Exception : ' + e);
                    },
                    onComplete: function (xhr)
                    {
                        var result = xhr.responseText;
                        $('j2t-temp-div').innerHTML = result.stripScripts();

                        var product_html = '';
                        if (j2t_product_essentials != ''){
                            if ($('j2t-temp-div').down('.'+j2t_product_essentials)){
                                product_html = $('j2t-temp-div').down('.'+j2t_product_essentials).innerHTML;
                            } else {
                                j2t_error += " - Unable to find "+j2t_product_essentials+" class element.\r\n";
                            }
                        } else {
                            if ($('j2t-temp-div').down('.product-essential')){
                                product_html = $('j2t-temp-div').down('.product-essential').innerHTML;
                            } else {
                                j2t_error += " - Unable to find product-essential class element.\r\n";
                            }
                        }

                        var txt_script = '';
                        var scripts = [];
                        var script_sources = xhr.responseText.split(/<script.*?>/);
                        for (var i=1; i < script_sources.length; i++){
                            var str = script_sources[i].split(/<\/script>/)[0];
                            str = str.replace('//<![CDATA[', '');
                            str = str.replace('//]]>', '');
                            if (str.indexOf('optionsPrice') != -1 || str.indexOf('spConfig') != -1 || /*str.indexOf('decorateGeneric') != -1 ||*/
                                str.indexOf('j2t_points') != -1 || str.indexOf('productAddToCartForm') != -1 ||
                                str.indexOf('DateOption') != -1){

                                str = str.replace('var optionsPrice', 'optionsPrice');
                                str = str.replace('var spConfig', 'spConfig');
                                str = str.replace('var DateOption', 'DateOption');

                                str = str.replace('var optionsPrice', 'optionsPrice');
                                str = str.replace('var productAddToCartForm', 'productAddToCartForm');
                                str = str.replace('this.form.submit()', 'sendcart(\'\', \'form\', 1, \'product_addtocart_form\')');

                                scripts.push(str);
                                txt_script += str + "\n";
                            }
                        }
                        
                        $('j2t-temp-div').innerHTML = '';
                        $('j2t_ajax_confirm').update('<div id="j2t_ajax_confirm_wrapper">'+j2t_prod_details+product_html+ '</div><script type="text/javascript">'+txt_script+'</script>');


                        if (j2t_product_image != ''){
                            if ($('j2t_ajax_confirm').down('.'+j2t_product_image)){
                                //$('j2t_ajax_confirm').down('.'+j2t_product_image).hide();
                                $('j2t_ajax_confirm').down('.'+j2t_product_image).remove();
                            } else {
                                j2t_error += " - Unable to find "+j2t_product_image+" class element.\r\n";
                            }
                        } else {
                            if ($('j2t_ajax_confirm').down('.product-img-box')){
                                //$('j2t_ajax_confirm').down('.product-img-box').hide();
                                $('j2t_ajax_confirm').down('.product-img-box').remove();
                            } else {
                                j2t_error += " - Unable to find product-img-box class element.\r\n";
                            }
                        }



                        var arr;
                        if (j2t_product_shop != ''){
                            if ($('j2t_ajax_confirm').down('.'+j2t_product_shop)){
                                arr = $('j2t_ajax_confirm').down('.'+j2t_product_shop).childElements();
                            } else {
                                j2t_error += " - Unable to find "+j2t_product_shop+" class element.\r\n";
                            }
                        } else {
                            if ($('j2t_ajax_confirm').down('.product-shop')){
                                arr = $('j2t_ajax_confirm').down('.product-shop').childElements();
                            } else {
                                j2t_error += " - Unable to find product-shop class element.\r\n";
                            }
                        }


                        arr.each(function(node){
                          node.style.display = 'none';
                        });


                        //J2T Bundle
                        //super-product-table
                        if ($('super-product-table')){
                            if ($('j2t_ajax_confirm').down('#super-product-table')){
                                $('j2t_ajax_confirm').down('#super-product-table').show();
                            } else {
                                j2t_error += " - Unable to find #super-product-table element.\r\n";
                            }
                            if ($('j2t_ajax_confirm').down('.add-to-box')){
                                $('j2t_ajax_confirm').down('.add-to-box').show();
                                if ($('j2t_ajax_confirm').down('.add-to-box').down('.add-to-links')){
                                    $('j2t_ajax_confirm').down('.add-to-box').down('.add-to-links').hide();
                                }

                                if ($('j2t_ajax_confirm').down('.add-to-box').down('.or')){
                                    $('j2t_ajax_confirm').down('.add-to-box').down('.or').hide();
                                }

                            } else {
                                j2t_error += " - Unable to find .add-to-box element.\r\n";
                            }
                        } //previous code is between else
                        else {
                            if (j2t_product_options != ''){
                                if ($('j2t_ajax_confirm').down('.'+j2t_product_options)){
                                    $('j2t_ajax_confirm').down('.'+j2t_product_options).show();
                                } else {
                                    j2t_error += " - Unable to find ."+j2t_product_options+".\r\n";
                                }
                            } else {
                                if ($('j2t_ajax_confirm').down('.product-options')){
                                    $('j2t_ajax_confirm').down('.product-options').show();
                                } else {
                                    j2t_error += " - Unable to find .product-options element.\r\n";
                                }
                            }

                            if (j2t_product_bottom != ''){
                                if ($('j2t_ajax_confirm').down('.'+j2t_product_bottom)){
                                    $('j2t_ajax_confirm').down('.'+j2t_product_bottom).show();
                                } else {
                                    j2t_error += " - Unable to find ."+j2t_product_bottom+" element.\r\n";
                                }
                            } else {
                                if ($('j2t_ajax_confirm').down('.product-options-bottom')){
                                    $('j2t_ajax_confirm').down('.product-options-bottom').show();
                                } else {
                                    j2t_error += " - Unable to find .product-options element.\r\n";
                                }

                            }

                        }
                        //End J2T Bundle


                        if (j2t_ajax_cart_debug == 1 && j2t_error != ""){
                            alert(j2t_error);
                            j2t_error = "";
                        }


                        replaceDelUrls();

                        //if (ajax_cart_show_popup){
                            showConfirm();
                        /*} else {
                            isLoading = false;
                            hideJ2tOverlay(false);
                        }*/
                    }

                });
                clearInterval(productDetailsIntervalOptions);
            } 
        },500);
        
        //////////////
    }
    
}


function sendcart(url, type, qty_to_insert, form_name){
    var continue_scr = true;
    if ($('pp_checkout_url')){
        //http://www.j2t-design.net
        var pp = $('pp_checkout_url').value;
        if (pp != ''){
            continue_scr = false;
            var form = $(form_name); //$('product_addtocart_form');
            form.submit();
        }
    }
    if (continue_scr) {
        
        hideJ2tOverlay(false);
        showLoading();
        if (type == 'form'){
            
            var found_file = false;
            var form = $(form_name); //$('product_addtocart_form');
            if (form){
                inputs = form.getInputs('file');
                if (inputs.length > 0){
                    found_file = true;
                }
            }

            if (found_file){
                form.submit();
            } else {
                url = ($(form_name).action).replace('checkout/cart', 'j2tajaxcheckout/index/cart/cart');
                var myAjax = new Ajax.Request(
                url,
                {
                    asynchronous: true,
                    method: 'post',
                    postBody: $(form_name).serialize(),
                    parameters : Form.serialize(form_name),
                    onException: function (xhr, e)
                    {
                        alert('Exception : ' + e);
                    },
                    onComplete: function (xhr)
                    {
                        $('j2t-temp-div').innerHTML = xhr.responseText;
                        var upsell_items = '';
                        
                        if ($('j2t-temp-div').down('.j2t-ajaxupsells')){
                            upsell_items = $('j2t-temp-div').down('.j2t-ajaxupsells').innerHTML;
                        } else {
                            j2t_error += " - Unable to find .j2t-ajaxupsells element.\r\n";
                        }
                        
                        
                        var return_message = '';
                        if ($('j2t-temp-div').down('.j2t_ajax_message')){
                            return_message = $('j2t-temp-div').down('.j2t_ajax_message').innerHTML;
                        } else {
                            j2t_error += " - Unable to find .j2t_ajax_message element.\r\n";
                        }
                        
                        var middle_text = '';                        
                        if ($('j2t-temp-div').down('.back-ajax-add')){
                            middle_text = '<div class="j2t-cart-bts">'+$('j2t-temp-div').down('.back-ajax-add').innerHTML+'</div>';
                        } else {
                            j2t_error += " - Unable to find .back-ajax-add element.\r\n";
                        }
                        
                        $('j2t_ajax_confirm').innerHTML = '<div id="j2t_ajax_confirm_wrapper">'+return_message + middle_text + upsell_items + '</div>';
                        var link_cart_txt = $('j2t-temp-div').down('.cart_content').innerHTML;
                        
                        if (j2t_ajax_cart_debug == 1 && j2t_error != ""){
                            alert(j2t_error);
                            j2t_error = "";
                        }

                        if ($$('#j2t_ajax_confirm_wrapper .messages .error-msg').length > 0){
                            if (ajax_cart_show_popup){
                                showConfirm();
                            } else {
                                isLoading = false;
                                hideJ2tOverlay(true);
                            }
                            $('j2t-temp-div').innerHTML = '';
                            return false;
                        }                       
                        
                        if ($$('.top-link-cart').length > 0){
                            $$('.top-link-cart').each(function (el){
                                el.innerHTML = link_cart_txt;
                            });
                        } else {
                            j2t_error += " - Unable to find .top-link-cart element.\r\n";
                        }
                        
                        if (j2t_custom_top_link != ''){
                            if ($$('.'+j2t_custom_top_link).length > 0){
                                $$('.'+j2t_custom_top_link).each(function (el){
                                    el.innerHTML = link_cart_txt;
                                });
                            } else {
                                j2t_error += " - Unable to find ."+j2t_custom_top_link+" element.\r\n";
                            }
                        }
                        
                        var mini_cart_txt = ''; 
                        
                        if ($('j2t-temp-div').down('.cart_side_ajax')){
                            mini_cart_txt = $('j2t-temp-div').down('.cart_side_ajax').innerHTML;
                        } else {
                            j2t_error += " - Unable to find .top-link-cart element.\r\n";
                        }
                       
                        if ($$('.mini-cart').length > 0){
                            $$('.mini-cart').each(function (el){
                                el.replace(mini_cart_txt);
                            });
                        } else {
                            j2t_error += " - Unable to find .mini-cart element.\r\n";
                        }
                        
                        if ($$('.block-cart').length > 0){
                            $$('.block-cart').each(function (el){
                                el.replace(mini_cart_txt);
                            });
                        } else {
                            j2t_error += " - Unable to find .block-cart element.\r\n";
                        }

                        if (j2t_custom_mini_cart != ''){
                            if ($$('.'+j2t_custom_mini_cart).length > 0){
                                $$('.'+j2t_custom_mini_cart).each(function (el){
                                    el.replace(mini_cart_txt);
                                });
                            } else {
                                j2t_error += " - Unable to find ."+j2t_custom_mini_cart+" element.\r\n";
                            }
                            
                        }
                        
                        
                        if (j2t_ajax_cart_debug == 1 && j2t_error != ""){
                            alert(j2t_error);
                            j2t_error = "";
                        }
                        
                        truncateOptions();
                        
                        replaceDelUrls();

                        if (ajax_cart_show_popup){
                            showConfirm();
                        } else {
                            isLoading = false;
                            hideJ2tOverlay(true);
                        }

                    }

                });
            }

        } else if (type == 'url'){
            //check if product has options
            //j2tajaxcart_url_check
            showLoading();
            //product/54
            var j2tReg = /\/product\/([0-9]+)/;
            //var j2tReg = new RegExp ( "\/product\/\d+", "gi" ) ;
            //if (url.search('/product/') != -1) {
            if (url.search(j2tReg) != -1) {
                //check if product has options
                //get product id
                var j2tMatches = url.match(j2tReg);
                //alert('ici');
                //alert(j2tMatches.length);
                if (j2tMatches[1] != undefined){
                    //alert(j2tMatches[1]);
                    var url_product_check =  j2tajaxcart_url_check.replace('product_id', j2tMatches[1]);
                    checkProductUrlJ2t(url_product_check, url, qty_to_insert);
                }
            } else {
                j2tSendCartUrl(url, qty_to_insert);
            }
        }
    }
}

function j2tSendCartUrl(url, qty_to_insert){
    url = url.replace('checkout/cart', 'j2tajaxcheckout/index/cart/cart');
    var myAjax = new Ajax.Request(
    url,
    {
        asynchronous: true,
        method: 'post',
        postBody: '',
        parameters: 'qty='+qty_to_insert,
        onException: function (xhr, e)
        {
            alert('Exception : ' + e);
        },
        onComplete: function (xhr)
        {
            $('j2t-temp-div').innerHTML = xhr.responseText;
            var upsell_items = '';
            if ($('j2t-temp-div').down('.j2t-ajaxupsells')){
                upsell_items = $('j2t-temp-div').down('.j2t-ajaxupsells').innerHTML;
            } else {
                j2t_error += " - Unable to find .j2t-ajaxupsells element.\r\n";
            }           
            
            
            var return_message = '';
            if ($('j2t-temp-div').down('.j2t_ajax_message')){
                return_message = $('j2t-temp-div').down('.j2t_ajax_message').innerHTML;
            } else {
                j2t_error += " - Unable to find .j2t_ajax_message element.\r\n";
            }
            
            var middle_text = ''; 
            if ($('j2t-temp-div').down('.back-ajax-add')){
                middle_text = '<div class="j2t-cart-bts">'+$('j2t-temp-div').down('.back-ajax-add').innerHTML+'</div>';
            } else {
                j2t_error += " - Unable to find .back-ajax-add element.\r\n";
            }

            var content_ajax = return_message + middle_text + upsell_items;

            $('j2t_ajax_confirm').innerHTML = '<div id="j2t_ajax_confirm_wrapper">'+content_ajax + '</div>';

            var link_cart_txt = '';
            if ($('j2t-temp-div').down('.cart_content')){
                link_cart_txt = $('j2t-temp-div').down('.cart_content').innerHTML;
            } else {
                j2t_error += " - Unable to find .cart_content element.\r\n";
            }
            
            
            if (j2t_ajax_cart_debug == 1 && j2t_error != ""){
                alert(j2t_error);
                j2t_error = "";
            }
            
            if ($$('#j2t_ajax_confirm_wrapper .messages .error-msg').length > 0){
                if (ajax_cart_show_popup){
                    showConfirm();
                } else {
                    isLoading = false;
                    hideJ2tOverlay(true);
                }
                return false;
            }
            
            if ($$('.top-link-cart').length > 0){
                $$('.top-link-cart').each(function (el){
                    el.innerHTML = link_cart_txt;
                });
            } else {
                j2t_error += " Unable to find .top-link-cart element. \r\n";
            }
            

            if (j2t_custom_top_link != ''){
                if ($$('.'+j2t_custom_top_link).length > 0){
                    $$('.'+j2t_custom_top_link).each(function (el){
                        el.innerHTML = link_cart_txt;
                    });
                } else {
                    j2t_error += " Unable to find ."+j2t_custom_top_link+" element. \r\n";
                }
                
            }

            var mini_cart_txt = '';
            
            if ($('j2t-temp-div').down('.cart_side_ajax')){
                mini_cart_txt = $('j2t-temp-div').down('.cart_side_ajax').innerHTML;
            } else {
                j2t_error += " Unable to find .cart_side_ajax element. \r\n";
            }
            
            
            if ($$('.mini-cart').length > 0){
                $$('.mini-cart').each(function (el){
                    el.replace(mini_cart_txt);
                    //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
                });
            } else {
                j2t_error += " Unable to find .mini-cart element. \r\n";
            }
            
            if ($$('.block-cart').length > 0){
                $$('.block-cart').each(function (el){
                    el.replace(mini_cart_txt);
                    //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
                });
            } else {
                j2t_error += " Unable to find .block-cart element. \r\n";
            }

            

            if (j2t_custom_mini_cart != ''){
                if ($$('.'+j2t_custom_mini_cart).length > 0){
                    $$('.'+j2t_custom_mini_cart).each(function (el){
                        el.replace(mini_cart_txt);
                    });
                } else {
                    j2t_error += " Unable to find ."+j2t_custom_mini_cart+" element. \r\n";
                }                
            }
            
            if (j2t_ajax_cart_debug == 1 && j2t_error != ""){
                alert(j2t_error);
                j2t_error = "";
            }
            
            truncateOptions();

            replaceDelUrls();
            if (ajax_cart_show_popup){
                showConfirm();
            } else {
                isLoading = false;
                hideJ2tOverlay(true);
            }
        }

    });
}

function checkProductUrlJ2tQty(url_check, url){
    //alert(url_check);
    var myAjax = new Ajax.Request(
    url_check,
    {
        asynchronous: true,
        method: 'post',
        postBody: '',
        onException: function (xhr, e)
        {
            alert('Exception : ' + e);
        },
        onComplete: function (xhr)
        {
            var return_value = xhr.responseText;
            if (return_value != 0){
                //alert(return_value);
                sendoptions(return_value);
            } else {
                sendQtyAsk(url);
            }   
        }
    });
}

function checkProductUrlJ2t(url_check, url, qty_to_insert){
    //alert(url_check);
    var myAjax = new Ajax.Request(
    url_check,
    {
        asynchronous: true,
        method: 'post',
        postBody: '',
        onException: function (xhr, e)
        {
            alert('Exception : ' + e);
        },
        onComplete: function (xhr)
        {
            var return_value = xhr.responseText;
            if (return_value != 0){
                //alert(return_value);
                sendoptions(return_value);
            } else {
                j2tSendCartUrl(url, qty_to_insert);
            }
            
        }

    });
}


function replaceDelUrls(){
    //if (!inCart){
        $$('a').each(function(el){
            if(el.href.search('checkout/cart/delete') != -1 && el.href.search('javascript:cartdelete') == -1){
                el.href = 'javascript:cartdelete(\'' + el.href +'\')';
            }
        });
    //}
}

function replaceAddUrls(){
    //if (!inCart){
        $$('a').each(function(link){
            if(link.href.search('checkout/cart/add') != -1){
                link.href = 'javascript:setLocation(\''+link.href+'\'); void(0);';
            }
        });
    //}    
}



function cartdelete(url){
    showLoading();
    url = url.replace('checkout/cart/delete', 'j2tajaxcheckout/index/cartdelete/cart/delete');
    var myAjax = new Ajax.Request(
    url,
    {
        asynchronous: true,
        method: 'post',
        postBody: 'btn_lnk=1',
        onException: function (xhr, e)
        {
            alert('Exception : ' + e);
        },
        onComplete: function (xhr)
        {
            $('j2t-temp-div').innerHTML = xhr.responseText;
            //$('j2t-temp-div').innerHTML = xhr.responseText;
            
            ////////////// scripts //////////////////////////
            
            var txt_script = '';
            var scripts = [];
            var script_sources = xhr.responseText.split(/<script.*?>/);
            for (var i=1; i < script_sources.length; i++){
                var str = script_sources[i].split(/<\/script>/)[0];
                str = str.replace('//<![CDATA[', '');
                str = str.replace('//]]>', '');
                if (str.indexOf('discount-coupon-form') != -1 || str.indexOf('giftcard-form') != -1){
                    
                    str = str.replace('var discountForm', 'discountForm');
                    str = str.replace('var giftcardForm', 'giftcardForm');
                    
                    scripts.push(str);
                    txt_script += str + "\n";
                }
            }
            
            ////////////// scripts //////////////////////////

            var cart_content = $('j2t-temp-div').down('.cart_content').innerHTML;
            
            $$('.top-link-cart').each(function (el){
                el.innerHTML = cart_content;
            });

            if (j2t_custom_top_link != ''){
                $$('.'+j2t_custom_top_link).each(function (el){
                    el.innerHTML = cart_content;
                });
            }
            
            
            

            var process_reload_cart = false;
            var full_cart_content = $('j2t-temp-div').down('.j2t_full_cart_content').innerHTML;
            
            $$('.cart').each(function (el){
                el.replace(full_cart_content + '<script type="text/javascript">'+txt_script+'</script>');
                process_reload_cart = true;
            });

            if (!process_reload_cart){
                $$('.checkout-cart-index .col-main').each(function (el){
                    el.replace(full_cart_content + '<script type="text/javascript">'+txt_script+'</script>');
                    //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
                });
            }


            if (j2t_custom_cart != ''){
                $$('.'+j2t_custom_cart).each(function (el){
                    el.replace(full_cart_content + '<script type="text/javascript">'+txt_script+'</script>');
                });
            }


            var cart_side = '';
            if ($('j2t-temp-div').down('.cart_side_ajax')){
                cart_side = $('j2t-temp-div').down('.cart_side_ajax').innerHTML;
            }

            
            $$('.mini-cart').each(function (el){
                el.replace(cart_side);
                //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
            });
            $$('.block-cart').each(function (el){
                el.replace(cart_side);
                //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
            });

            if (j2t_custom_mini_cart != ''){
                $$('.'+j2t_custom_mini_cart).each(function (el){
                    el.replace(cart_side);
                });
            }
            
            $('j2t-temp-div').innerHTML = '';

            replaceDelUrls();
            
            isLoading = false;
            hideJ2tOverlay(true);
            $('j2t-temp-div').innerHTML = '';
        }

    });
}

function showJ2tProductDetails(url){
    $('j2t-product-details').innerHTML = '';
    var j2tReg = /(\/product\/([0-9]+)|options=cart)/;
    if (url.search(j2tReg) != -1) {
        //check if product has options
        //get product id
        var j2tMatches = url.match(j2tReg);
        
        if (url.search(/options=cart/i) != -1){
            var arr_url = url.split('/');
            //alert(arr_url[(arr_url.length - 1)]);
            var url_key = arr_url[(arr_url.length - 1)];
            url_key = url_key.replace('options=cart','');
            url_key = url_key.replace('.html','');
            j2tMatches[1] = 'j2t-url-product-'+url_key;
        }
            
        if (j2tMatches[1] != undefined){
            if (j2t_ajax_cart_show_details == 1){
                waitProductDetails = true;
                var url_product_details =  j2tajaxcart_url_product_details.replace('product_id', j2tMatches[1]);
                var url_post_details = url.replace('?options=cart','');
                var myAjax = new Ajax.Request(
                url_product_details,
                {
                    asynchronous: true,
                    method: 'post',
                    postBody: 'full_url='+url_post_details+'&current_store_id='+j2t_current_store_id,
                    parameters : 'full_url='+url_post_details+'&current_store_id='+j2t_current_store_id,
                    onException: function (xhr, e)
                    {
                        alert('Exception : ' + e);
                    },
                    onComplete: function (xhr)
                    {
                        var return_value = xhr.responseText;
                        //return return_value;
                        //j2t_ajax_cart_show_details
                        $('j2t-product-details').update(return_value);
                        $$('j2t-product-details').each(function (el){
                            el.update(return_value);
                        });
                        waitProductDetails = false;
                    }
                });
            } 
        }
    }
}

function showJ2tOverlay(){
    //new Effect.Appear($('j2t-overlay'), { duration: 0.5,  to: 0.8 });
    new Effect.Appear($('j2t-overlay'), { duration: 0.5,  to: j2t_ajax_cart_transparency });
    
}

function hideJ2tOverlay(effect_load){
    if (!isLoading){
        $('j2t-overlay').hide();
        $('j2t_ajax_progress').hide();
        $('j2t_ajax_confirm').hide();
        
        if (effect_load && j2t_blink != ''){
            $$(j2t_blink).each(function (el) {Effect.Pulsate(el, { pulses: 1, duration: 0.5 });});
        }
    }
}


function j2tCenterWindow(element) {
     if($(element) != null) {

          // retrieve required dimensions
            var el = $(element);
            var elDims = el.getDimensions();
            var browserName=navigator.appName;
            if(browserName==="Microsoft Internet Explorer") {

                if(document.documentElement.clientWidth==0) {
                    //IE8 Quirks
                    //alert('In Quirks Mode!');
                    var y=(document.viewport.getScrollOffsets().top + (document.body.clientHeight - elDims.height) / 2);
                    var x=(document.viewport.getScrollOffsets().left + (document.body.clientWidth - elDims.width) / 2);
                }
                else {
                    var y=(document.viewport.getScrollOffsets().top + (document.documentElement.clientHeight - elDims.height) / 2);
                    var x=(document.viewport.getScrollOffsets().left + (document.documentElement.clientWidth - elDims.width) / 2);
                }
            }
            else {
                // calculate the center of the page using the browser andelement dimensions
                var y = Math.round(document.viewport.getScrollOffsets().top + ((window.innerHeight - $(element).getHeight()))/2);
                var x = Math.round(document.viewport.getScrollOffsets().left + ((window.innerWidth - $(element).getWidth()))/2);
            }
            // set the style of the element so it is centered
            var styles = {
                position: 'absolute',
                top: y + 'px',
                left : x + 'px'
            };
            el.setStyle(styles);
     }
}


function generateTemplateBox(content, box_w, box_h){
    //var use_template = true;
    //var box_width_height = 20;
    if (use_template){
        var middle_w = box_w - (box_width_height * 2);
        var middle_h = box_h + (box_width_height * 2);

        //$('j2t-div-template').down('.j2t-box-cm').innerHTML = content;
        $('j2t-div-template').down('.j2t-box-cm').innerHTML = '<div class="inner-ajax-content" id="j2t_inner_ajax_content" style="position:relative;">'+content+"</div>";

        $('j2t-div-template').down('.j2t-box-tl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-tm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-tr').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});

        $('j2t-div-template').down('.j2t-box-cl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': middle_h+'px'});
        $('j2t-div-template').down('.j2t-box-cm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': middle_h+'px'});
        $('j2t-div-template').down('.j2t-box-cr').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': middle_h+'px'});

        $('j2t-div-template').down('.j2t-box-bl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-bm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-br').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});

        content = $('j2t-div-template').innerHTML;
    } else {
        content = '<div class="inner-ajax-content" id="j2t_inner_ajax_content" style="position:relative;">'+content+"</div>";
    }
    return content;
}


function showLoading(){
    isLoading = true;
    showJ2tOverlay();
    var progress_box = $('j2t_ajax_progress');
    progress_box.show();
    progress_box.style.width = loadingW + 'px';
    progress_box.style.height = loadingH + 'px';

    //width : 320 height : 140
    //312 x 102

    if (use_template){
        progress_box.style.width = loadingW + (box_width_height * 2) + 'px';
        progress_box.style.height = loadingH + (box_width_height * 2) + 'px';
    }
    //alert($('j2t_ajax_progress').getWidth() +' et '+ $('j2t_ajax_progress').getHeight());

    $('j2t_ajax_progress').innerHTML = generateTemplateBox($('j2t-loading-data').innerHTML, $('j2t_ajax_progress').getWidth()-box_width_height, $('j2t_ajax_progress').getHeight()-(box_width_height*2));
    progress_box.style.position = 'absolute';
    
    var padding_height = $('j2t_ajax_progress').getHeight() / 2;
    padding_height -= $('j2t_ajax_progress').down('.j2t-ajax-child').getHeight() / 2;
    var styles = {
        paddingTop: Math.round(padding_height)+'px'
    };
    
    $('j2t_ajax_progress').down('.j2t-ajax-child').setStyle(styles);

    j2tCenterWindow(progress_box);
}


function showConfirm(){
    isLoading = false;
    showJ2tOverlay();
    $('j2t_ajax_progress').hide();
    var confirm_box = $('j2t_ajax_confirm');
    confirm_box.show();
    confirm_box.style.width = confirmW + 'px';
    confirm_box.style.height = confirmH + 'px';
    //j2t_ajax_confirm_wrapper
    if ($('j2t_ajax_confirm_wrapper') && $('j2t-upsell-product-table')){
        //alert($('j2t_ajax_confirm_wrapper').getHeight());
        confirm_box.style.height = $('j2t_ajax_confirm_wrapper').getHeight() + 'px';
        decorateTable('j2t-upsell-product-table');
    }

    if (use_template){
        confirm_box.style.width = $('j2t_ajax_confirm_wrapper').getWidth() + (box_width_height * 2) + 'px';
        confirm_box.style.height = $('j2t_ajax_confirm_wrapper').getHeight() + (box_width_height * 4) + 'px';
    }

    $('j2t_ajax_confirm_wrapper').replace('<div id="j2t_ajax_confirm_wrapper">'+generateTemplateBox($('j2t_ajax_confirm_wrapper').innerHTML, $('j2t_ajax_confirm_wrapper').getWidth(), $('j2t_ajax_confirm_wrapper').getHeight())+'<div>');
    
    
    if (j2t_show_close){
        $('j2t_ajax_confirm_wrapper').insert('<div id="j2t-closing-button" class="j2t-closing-button"><span>x</span></div>');
        $('j2t-closing-button').stopObserving();
        Event.observe($('j2t-closing-button'), 'click', function(){hideJ2tOverlay(true)});
    }
    
    

    confirm_box.style.position = 'absolute';
    j2tCenterWindow(confirm_box);
}


function correctSizeConfirm() {
    //$('j2t_ajax_confirm_wrapper').getWidth(), $('j2t_ajax_confirm_wrapper').getHeight()
    if ($('j2t_ajax_confirm_wrapper')){
        //alert($('j2t_ajax_confirm').style.display);
        if ($('j2t_ajax_confirm').style.display != "none"){
            
            var current_box_w = $('j2t_ajax_confirm').down('.inner-ajax-content').getWidth();
            var current_box_h = $('j2t_ajax_confirm').down('.inner-ajax-content').getHeight();
            
            //alert(current_box_h);

            if (saved_wrapper_h != current_box_h){
                saved_wrapper_w = current_box_w;
                saved_wrapper_h = current_box_h;
                var confirm_box = $('j2t_ajax_confirm');
                var confirm_wrapper_box = $('j2t_ajax_confirm');
                if (use_template){
                    var middle_w = $('j2t_ajax_confirm').getWidth() - (box_width_height * 2);
                    var middle_h = current_box_h-(box_width_height*2) + (box_width_height * 2);
                    
                    
                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-tl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});
                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-tm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': box_width_height+'px'});
                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-tr').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});

                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-cl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': middle_h+'px'});
                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-cm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': middle_h+'px'});
                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-cr').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': middle_h+'px'});

                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-bl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});
                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-bm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': box_width_height+'px'});
                    $('j2t_ajax_confirm_wrapper').down('.j2t-box-br').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});

                    confirm_box.style.height = (box_width_height * 2) + middle_h + 'px';
                    confirm_wrapper_box.style.height = (box_width_height * 2) + middle_h + 'px';
                } else {
                    confirm_box.style.height = $('j2t_ajax_confirm_wrapper').getHeight() + 'px';
                    confirm_wrapper_box.style.height = (box_width_height * 2) + middle_h + 'px';
                }
            }
        }
    }
}


Event.observe(window, 'resize', function(){
    var confirm_box = $('j2t_ajax_confirm');
    j2tCenterWindow(confirm_box);

    var progress_box = $('j2t_ajax_progress');
    j2tCenterWindow(progress_box);
});

Event.observe(window, 'scroll', function(){
    var confirm_box = $('j2t_ajax_confirm');
    j2tCenterWindow(confirm_box);

    var progress_box = $('j2t_ajax_progress');
    j2tCenterWindow(progress_box);
});

document.observe("dom:loaded", function() {
    replaceDelUrls();
    replaceAddUrls();
    Event.observe($('j2t-overlay'), 'click', function(){hideJ2tOverlay(true)});

    var cartInt = setInterval(function(){
        if (typeof productAddToCartForm  != 'undefined'){
            if ($('j2t-overlay')){
                Event.observe($('j2t-overlay'), 'click', function(){hideJ2tOverlay(true)});
            }
            productAddToCartForm.submit = function(url){
                if(this.validator && this.validator.validate()){
                    sendcart('', 'form', 1, 'product_addtocart_form');
                }
                clearInterval(cartInt);
                return false;
            }
        } else {
            clearInterval(cartInt);
        }
    },500);
    
    setInterval(function(){ correctSizeConfirm(); },100);

    var form = $('product_addtocart_form');
    if(form){
        inputs = form.getInputs('file');

        if (inputs.length == 0){
            Event.observe("product_addtocart_form", "submit", function(event){
                event.stop();
            });
        }
    }
    
});
