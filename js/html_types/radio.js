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

    jQuery.each(woof_current_values, function (index, value) {
        if (index == name) {
            delete woof_current_values[name];
            return;
        }
    });

    if (slug != 0) {
        woof_current_values[name] = slug;
        jQuery('a.woof_radio_term_reset[name=' + name + ']').hide();
        jQuery('input[name=' + name + ']').filter(':checked').parents('li').find('a.woof_radio_term_reset').show();
        jQuery('input[name=' + name + ']').parents('ul.woof_list_radio').find('label').css({'fontWeight': 'normal'});
        jQuery('input[name=' + name + ']').filter(':checked').parents('li').find('label').css({'fontWeight': 'bold'});
    } else {
        jQuery('a.woof_radio_term_reset[name=' + name + ']').hide();
        jQuery('input[name=' + name + ']').attr('checked', false);
        jQuery('input[name=' + name + ']').parent().removeClass('checked');
        jQuery('input[name=' + name + ']').parents('ul.woof_list_radio').find('label').css({'fontWeight': 'normal'});
    }


    if (woof_autosubmit) {
        window.location = woof_get_submit_link();
    }
}

