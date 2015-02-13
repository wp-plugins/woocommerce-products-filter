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
    var link = woof_current_page_link + "?swoof=1";
    //+++
    if (jQuery(woof_current_values).size() > 0) {
        jQuery.each(woof_current_values, function (index, value) {
            if (index == 'swoof') {
                return;
            }
            //***
            if (index != name) {
                link = link + "&" + index + "=" + value;
            }
        });
    }
    if (slug != 0) {
        link = link + "&" + name + "=" + slug;
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

function woof_init_selects() {
    try {
        // jQuery("select.woof_select").chosen('destroy').trigger("liszt:updated");
        jQuery("select.woof_select").chosen({disable_search_threshold: 10});
    } catch (e) {

    }
}

