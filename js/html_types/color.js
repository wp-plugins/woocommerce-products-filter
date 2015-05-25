jQuery(function () {
    //woof_init_colors();
});

function woof_init_colors() {
    //http://jsfiddle.net/jtbowden/xP2Ns/
    jQuery('.woof_color_term').each(function () {
        var span = jQuery('<span style="background-color:' + jQuery(this).data('color') + '" class="' + jQuery(this).attr('type') + ' ' + jQuery(this).attr('class') + '"></span>').click(woof_color_do_check).mousedown(woof_color_do_down).mouseup(woof_color_do_up);
        if (jQuery(this).is(':checked')) {
            span.addClass('checked');
        }
        jQuery(this).wrap(span).hide();
    });

    function woof_color_do_check() {
        var is_checked = false;
        if (jQuery(this).hasClass('checked')) {
            jQuery(this).removeClass('checked');
            jQuery(this).children().prop("checked", false);
        } else {
            jQuery(this).addClass('checked');
            jQuery(this).children().prop("checked", true);
            is_checked = true;
        }

        woof_color_process_data(this, is_checked);
    }

    function woof_color_do_down() {
        jQuery(this).addClass('clicked');
    }

    function woof_color_do_up() {
        jQuery(this).removeClass('clicked');
    }
}

function woof_color_process_data(_this, is_checked) {
    var tax = jQuery(_this).find('input[type=checkbox]').data('tax');
    var name = jQuery(_this).find('input[type=checkbox]').attr('name');
    woof_color_direct_search(name, tax, is_checked);
}

function woof_color_direct_search(name, tax, is_checked) {

    var values = '';

    if (is_checked) {
        if (tax in woof_current_values) {
            woof_current_values[tax] = woof_current_values[tax] + ',' + name;
        } else {
            woof_current_values[tax] = name;
        }
        jQuery('.woof_color_term[name=' + name + ']').attr('checked', true);
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
        jQuery('.woof_color_term[name=' + name + ']').attr('checked', false);
    }

    woof_ajax_page_num = 1;
    if (woof_autosubmit) {
        woof_submit_link(woof_get_submit_link());
    }
}


