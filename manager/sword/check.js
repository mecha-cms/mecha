/**
 * Custom Radio and Checkbox
 * -------------------------
 */

(function($, base) {

    var $checkbox = $('input[type="checkbox"]'),
        $radio = $('input[type="radio"]');

    if (!$checkbox.length && !$radio.length) return;

    if ($checkbox.length) {
        $checkbox.each(function() {
            $(this).before('<a class="checkbox' + (this.checked ? ' checked' : "") + '" href="#toggle"></a>');
        }).on("change", function(e) {
            $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked');
            base.fire('on_checkbox_change', [e, this]);
            base.fire('on_checkbox_' + (this.checked ? 'check' : 'uncheck'), [e, this]);
        });
        $('.checkbox').on("click", function() {
            if ($(this).is('.disabled')) return false;
            $(this).toggleClass('checked').next().prop('checked', $(this).is('.checked')).trigger("change");
            return false;
        }).on("mousedown", function() {
            return false;
        });
        $checkbox.filter(':disabled').prev().addClass('disabled');
    }

    if ($radio.length) {
        $radio.each(function() {
            $(this).before('<a class="radio' + (this.checked ? ' checked' : "") + '" href="#check"></a>');
        }).on("change", function(e) {
            $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked');
            $radio.filter('[name="' + this.name + '"]').not(this).prop('checked', false).prev().removeClass('checked');
            base.fire('on_radio_change', [e, this]);
            base.fire('on_radio_' + (this.checked ? 'check' : 'uncheck'), [e, this]);
        });
        $('.radio').on("click", function() {
            if ($(this).is('.disabled') || $(this).is('.checked')) return false;
            $(this).next().prop('checked', true).trigger("change");
            return false;
        }).on("mousedown", function() {
            return false;
        });
        $radio.filter(':disabled').prev().addClass('disabled');
    }

})(Zepto, DASHBOARD);