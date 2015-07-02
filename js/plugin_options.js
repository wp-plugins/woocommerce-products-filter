jQuery(function () {
    jQuery('.js_woof_add_options').click(function () {
        var taxonomy = jQuery(this).data('taxonomy');
        var taxonomy_name = jQuery(this).data('taxonomy-name');
        var info = jQuery("#woof-modal-content");
        info.dialog({
            'title': taxonomy_name,
            'widht': 600,
            'height': 400,
            'dialogClass': 'wp-dialog',
            'modal': true,
            'autoOpen': false,
            'closeOnEscape': true,
            open: function () {
                jQuery.each(jQuery('#woof-modal-content .woof_popup_option'), function (index, value) {
                    var option = jQuery(this).data('option');
                    var val = jQuery('input[name="woof_settings[' + option + '][' + taxonomy + ']"]').val();
                    jQuery(this).val(val);
                });
            },
            'buttons': {
                "Close": function () {
                    jQuery.each(jQuery('#woof-modal-content .woof_popup_option'), function (index, value) {
                        var option = jQuery(this).data('option');
                        var val = jQuery(this).val();
                        jQuery('input[name="woof_settings[' + option + '][' + taxonomy + ']"]').val(val);
                        console.log(val);
                    });
                    //+++
                    jQuery(this).dialog('close');
                }
            }
        });


        info.dialog('open');

        return false;
    });
});