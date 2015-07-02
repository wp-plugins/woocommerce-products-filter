jQuery(function () {
    //woof_init_mselects();
});

function woof_init_mselects() {
    try {
        // jQuery("select.woof_select").chosen('destroy').trigger("liszt:updated");
        jQuery("select.woof_mselect").chosen({disable_search_threshold: 10});
    } catch (e) {

    }

    jQuery('.woof_mselect').change(function () {
        var slug = jQuery(this).val();
        var name = jQuery(this).attr('name');
        woof_mselect_direct_search(name, slug);
    });
}

function woof_mselect_direct_search(name, slug) {
    //mode with Filter button
    var values = [];
    jQuery('.woof_mselect[name=' + name + '] option:selected').each(function (i, v) {
        values.push(jQuery(this).val());
    });
    values = values.join(',');
    if (values.length) {
        woof_current_values[name] = values;
    } else {
        delete woof_current_values[name];
    }
    
    woof_ajax_page_num = 1;
    if (woof_autosubmit) {
        woof_submit_link(woof_get_submit_link());
    }
}


