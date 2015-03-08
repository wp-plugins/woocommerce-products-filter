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
    
    jQuery('.woof_checkbox_term, .woof_checkbox_instock').iCheck({
        checkboxClass: 'icheckbox_' + icheck_skin.skin + '-' + icheck_skin.color,
        //checkboxClass: 'icheckbox_square-green'
    });

    jQuery('.woof_checkbox_term').on('ifChecked', function (event) {
        jQuery(this).attr("checked", true);
        woof_checkbox_process_data(this, true);
    });

    jQuery('.woof_checkbox_term').on('ifUnchecked', function (event) {
        jQuery(this).attr("checked", false);
        woof_checkbox_process_data(this, false);
    });
});

function woof_checkbox_process_data(_this, is_checked) {
    var tax = jQuery(_this).data('tax');
    var name = jQuery(_this).attr('name');
    woof_checkbox_direct_search(name, tax, is_checked);
}

function woof_checkbox_direct_search(name, tax, is_checked) {

    var values = '';

    if (is_checked) {
        if (tax in woof_current_values) {
            woof_current_values[tax] = woof_current_values[tax] + ',' + name;
        } else {
            woof_current_values[tax] = name;
        }
        jQuery('.woof_checkbox_term[name=' + name + ']').attr('checked', true);
    } else {
        values = woof_current_values[tax];
        values = values.split(',');
        var tmp = [];
        jQuery.each(values, function (index, value) {
            if (value != name) {
                tmp.push(value);
            }
        });
        values = tmp;
        if (values.length) {
            woof_current_values[tax] = values.join(',');
        } else {
            delete woof_current_values[tax];
        }
        jQuery('.woof_checkbox_term[name=' + name + ']').attr('checked', false);
    }


    if (woof_autosubmit) {
        window.location = woof_get_submit_link();
    }
}


