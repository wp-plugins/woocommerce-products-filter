var woof_edit_view = false;
var woof_current_conatiner_class = '';
var woof_current_containers_data = {};

jQuery(function () {
    jQuery('.woof_edit_view').click(function () {
        woof_edit_view = true;
        var sid = jQuery(this).data('sid');
        var css_class = 'woof_sid_' + sid;
        jQuery(this).next('div').html(css_class);
        //+++
        jQuery("." + css_class + " .woof_container_overlay_item").show();
        jQuery("." + css_class + " .woof_container").addClass('woof_container_overlay');
        jQuery.each(jQuery("." + css_class + " .woof_container_overlay_item"), function (index, ul) {
            jQuery(this).html(jQuery(this).parents('.woof_container').data('css-class'));
        });


        return false;
    });
});

/*
 jQuery(function () {
 
 jQuery('.woof_edit_view').click(function () {
 var sid = jQuery(this).data('sid');
 var css_class = 'woof_sid_' + sid;
 woof_edit_view = true;
 jQuery("." + css_class + " .woof_container").addClass('woof_container_overlay');
 //jQuery('.woof_container_overlay').plainOverlay('show', {duration: 500});
 //+++
 jQuery('.woof_container_overlay').prepend('<a href="#" class="woof_container_setter_link">e</a>');
 
 
 
 jQuery.each(jQuery('.woof_container_setter_link'), function (index, a) {
 jQuery(a).click(function () {
 woof_current_conatiner_class = jQuery(this).parent().data('css-class');
 var info = jQuery(this).parent().find('.woof_container_overlay_item');
 info.dialog({
 'title': woof_current_conatiner_class,
 'widht': 600,
 'height': 400,
 'dialogClass': 'wp-dialog',
 'modal': false,
 'autoOpen': false,
 'closeOnEscape': true,
 open: function () {
 var myOptions = {
 0: '---',
 1: 1,
 0.75: 0.75,
 0.50: 0.50,
 0.33: 0.33,
 0.25: 0.25
 };
 var _select = jQuery('<select class="woof_container_width_setter" onchange="woof_change_cont_width(this)">');
 jQuery.each(myOptions, function (val, text) {
 _select.append(
 jQuery('<option></option>').val(val).html(text)
 );
 });
 jQuery(this).append('<h4>Container width</h4>');
 jQuery(this).append(_select);
 },
 close: function () {
 jQuery(a).append('<div class="woof_container_overlay_item"></div>');
 },
 'buttons': {
 "Close": function () {
 jQuery(this).dialog('close');
 }
 }
 });
 
 
 info.dialog('open');
 
 return false;
 });
 });
 
 
 
 
 return false;
 });
 //***
 
 });
 */
function woof_change_cont_width(select) {
    var width = parseFloat(jQuery(select).val()) * 100;
    console.log(width);
    console.log(woof_current_conatiner_class);
    jQuery('.' + woof_current_conatiner_class).css('width', width + '%');
}