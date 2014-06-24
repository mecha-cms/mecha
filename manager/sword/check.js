/**
 * Custom Radio and Checkbox
 * -------------------------
 */

(function($, base) {

    var $checkbox = $('input[type="checkbox"]'),
        $radio = $('input[type="radio"]');

    if (!$checkbox.length && !$radio.length) return;

    $radio.each(function() {
        $(this).before('<a class="radio' + (this.checked ? ' checked' : "") + '" href="#"></a>');
    }).on("change", function() {
        $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked');
    });

    $checkbox.each(function() {
        $(this).before('<a class="checkbox' + (this.checked ? ' checked' : "") + '" href="#"></a>');
    }).on("change", function() {
        $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked');
    });

    $('.radio').on("click", function(e) {
        if ($(this).is('.checked') || $(this).next().is(':disabled')) return false;
        $(this).addClass('checked').siblings().removeClass('checked');
        $(this).next().prop('checked', true).trigger("change").siblings('[name="' + $(this).next().attr('name') + '"]').prop('checked', false).trigger("change");
        base.fire('on_radio_change', [e, this]);
        base.fire('on_radio_checked', [e, this]);
        return false;
    }).on("mousedown", function() {
        return false;
    });

    $('.checkbox').on("click", function(e) {
        if ($(this).next().is(':disabled')) return false;
        $(this).toggleClass('checked').next().prop('checked', $(this).is('.checked')).trigger("change");
        base.fire('on_checkbox_change', [e, this]);
        base.fire('on_checkbox_' + ($(this).is('.checked') ? 'checked' : 'unchecked'), [e, this]);
        return false;
    }).on("mousedown", function() {
        return false;
    });

    $radio.filter(':disabled').prev().addClass('disabled');
    $checkbox.filter(':disabled').prev().addClass('disabled');

})(Zepto, DASHBOARD);