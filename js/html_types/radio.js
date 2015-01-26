jQuery(function () {
    //+++
    /*
     jQuery('.woof_term').click(function () {
     var slug = jQuery(this).data('slug');
     var name = jQuery(this).attr('name');
     woof_radio_direct_search(name, slug);
     });
     */
    //***
    jQuery('.woof_radio_term').iCheck({
        radioClass: 'iradio_' + icheck_skin.skin + '-' + icheck_skin.color,
        //radioClass: 'iradio_square-green'        
    });

    jQuery('.woof_radio_term').on('ifChecked', function (event) {
        jQuery(this).attr("checked", true);
        var slug = jQuery(this).data('slug');
        var name = jQuery(this).attr('name');
        woof_radio_direct_search(name, slug);
    });

    jQuery('.woof_radio_term_reset').click(function () {
        woof_radio_direct_search(jQuery(this).attr('name'), 0);
        return false;
    });
});

function woof_radio_direct_search(name, slug) {
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

    if (slug !== 0) {
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

