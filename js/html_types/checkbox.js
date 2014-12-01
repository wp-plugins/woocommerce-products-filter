jQuery(function () {
    //+++
    /*
     jQuery('.woof_checkbox_term').click(function () {
     var slug = jQuery(this).data('slug');
     var name = jQuery(this).attr('name');
     woof_radio_direct_search(name, slug);
     });
     */
    //***
    jQuery('.woof_checkbox_term').iCheck({
        checkboxClass: 'icheckbox_' + icheck_skin.skin + '-' + icheck_skin.color,
        //checkboxClass: 'icheckbox_square-green'
    });

    jQuery('.woof_checkbox_term').on('ifChecked', function (event) {
        jQuery(this).attr("checked", true);
        woof_checkbox_process_data(this, true);
    });

    jQuery('.woof_checkbox_term').on('ifUnchecked', function (event) {
        jQuery(this).attr("checked", true);
        woof_checkbox_process_data(this, false);
    });
});

function woof_checkbox_process_data(_this, is_checked) {
    var tax = jQuery(_this).data('tax');
    var name = jQuery(_this).attr('name');
    woof_checkbox_direct_search(name, tax, is_checked);
}

function woof_checkbox_direct_search(name, tax, is_checked) {
    var link = woof_current_page_link + "?swoof=1";
    //+++
    if (jQuery(woof_current_values).size() > 0) {
        if (!is_checked) {
            var value = woof_current_values[tax];

            var n = value.search(',');
            if (n) {
                var values = value.split(',');

                var tmp = [];
                for (var i = 0; i < values.length; i++) {
                    if (values[i] != name) {
                        tmp.push(values[i]);
                    }
                }

                if (tmp.length) {
                    woof_current_values[tax] = tmp.join(',');
                } else {
                    delete woof_current_values[tax];
                }
            }

        }
        //+++    
        jQuery.each(woof_current_values, function (index, value) {
            if (index == 'swoof') {
                return;
            }
            //***

            if (index != tax) {
                // if (is_checked) {
                link = link + "&" + index + "=" + value;
                // }
            } else {
                var values = value;
                if (is_checked) {
                    values = value + ',' + name;
                }
                values = values.split(',');
                values = jQuery.unique(values);
                values = values.join(',');
                link = link + "&" + index + "=" + values;
            }

        });
    }
    //+++
    if (jQuery(woof_current_values).size() == 0 || woof_current_values[tax] === undefined && is_checked) {
        link = link + "&" + tax + "=" + name;
    }
    //sanitize link for '?swoof=1' only
    var tmp_link = link.split('?swoof=1');
    try {
        if (tmp_link[1].length == 0) {
            link = tmp_link[0];
        }
    } catch (e) {

    }
    //+++
    window.location = link;
}

