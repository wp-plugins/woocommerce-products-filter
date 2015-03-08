jQuery(function () {
    woof_init_selects();
    //***
    jQuery('.woof_select').change(function () {
        var slug = jQuery(this).val();
        var name = jQuery(this).attr('name');
        woof_select_direct_search(name, slug);
    });

});

function woof_select_direct_search(name, slug) {

    jQuery.each(woof_current_values, function (index, value) {
        if (index == name) {
            delete woof_current_values[name];
            return;
        }
    });

    if (slug != 0) {
        woof_current_values[name] = slug;
    }


    if (woof_autosubmit) {
        window.location = woof_get_submit_link();
    }

}

function woof_init_selects() {
    if (is_woof_use_chosen) {
        try {
            // jQuery("select.woof_select").chosen('destroy').trigger("liszt:updated");
            jQuery("select.woof_select").chosen({disable_search_threshold: 10});
        } catch (e) {

        }
    }
}

