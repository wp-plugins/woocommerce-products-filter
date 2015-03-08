jQuery(function () {
    var containers = jQuery('.woof_container');

    try {
        jQuery.each(containers, function (index, value) {

            var remove = false;

            if (jQuery(value).find('ul.woof_list_radio').size() === 1) {
                remove = true;
            }

            if (jQuery(value).find('ul.woof_list_checkbox').size() === 1) {
                remove = true;
            }

            if (remove) {
                if (jQuery(value).find('ul.woof_list li').size() === 0) {
                    jQuery(value).remove();
                }
            }

        });
    } catch (e) {

    }
    //+++
    jQuery('.woo_submit_search_form').click(function () {
        window.location = woof_get_submit_link();
        return false;
    });

    //fix for native woo price range
    jQuery('.widget_price_filter form').submit(function () {
        var min_price = jQuery(this).find('.price_slider_amount #min_price').val();
        var max_price = jQuery(this).find('.price_slider_amount #max_price').val();
        woof_current_values.min_price = min_price;
        woof_current_values.max_price = max_price;
        //var link = woof_get_submit_link();
        //link = link + '&min_price=' + min_price + '&max_price=' + max_price;
        window.location = woof_get_submit_link();
        return false;
    });

    //***
    jQuery('ul.woof_childs_list').parent('li').addClass('woof_childs_list_li');
    //***

    jQuery('.woof_checkbox_instock').on('ifChecked', function (event) {
        jQuery(this).attr("checked", true);
        woof_current_values.stock = 'instock';
        if (woof_autosubmit) {
            window.location = woof_get_submit_link();
        }
    });

    jQuery('.woof_checkbox_instock').on('ifUnchecked', function (event) {
        jQuery(this).attr("checked", false);
        delete woof_current_values.stock;
        if (woof_autosubmit) {
            window.location = woof_get_submit_link();
        }
    });

});

function woof_get_submit_link() {
    //filter woof_current_values values
    if (Object.keys(woof_current_values).length > 0) {
        jQuery.each(woof_current_values, function (index, value) {
            if (index == 'swoof') {
                delete woof_current_values[index];
            }
            if (index == 's') {
                delete woof_current_values[index];
            }
        });
    }
    //***
    if (Object.keys(woof_current_values).length === 2) {
        if (('min_price' in woof_current_values) && ('max_price' in woof_current_values)) {
            return woof_current_page_link + '?min_price=' + woof_current_values.min_price + '&max_price=' + woof_current_values.max_price;
        }
    }


    if (Object.keys(woof_current_values).length === 1) {
        if ('stock' in woof_current_values) {
            //return woof_current_page_link;
        }
    }

    if (Object.keys(woof_current_values).length === 0) {
        return woof_current_page_link;
    }
    //+++
    var link = woof_current_page_link + "?swoof=1";
    if (Object.keys(woof_current_values).length > 0) {
        jQuery.each(woof_current_values, function (index, value) {
            link = link + "&" + index + "=" + value;
        });
    }

    return link;
}

