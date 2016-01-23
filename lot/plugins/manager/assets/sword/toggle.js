/**
 * Custom Checkbox and Radio
 * -------------------------
 */

(function(base, $) {

    var $checkbox = $('input[type="checkbox"]'),
        $radio = $('input[type="radio"]');

    if (!$checkbox.length && !$radio.length) return;

    if ($checkbox.length) {
        $checkbox.each(function() {
            if (!$(this).prev().is('.checkbox')) {
                $(this).before('<a class="checkbox' + (this.checked ? ' checked' : "") + '" href="#toggle"></a>');
            }
        }).on("change", function(e) {
            var data = {
                'event': e,
                'target': this
            };
            if ($(this).is('[disabled]') || $(this).is('[readonly]')) return false;
            $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked');
            base.fire('on_checkbox_change', data);
            base.fire('on_checkbox_' + (this.checked ? 'check' : 'uncheck'), data);
        });
        $('.checkbox').on("click", function() {
            if ($(this).is('.disabled') || $(this).is('.readonly')) return false;
            $(this).toggleClass('checked').next().prop('checked', $(this).is('.checked')).trigger("change");
            return false;
        }).on("mousedown", false);
        $checkbox.filter('[disabled]').prev().addClass('disabled').closest('label').on("click mousedown", false);
        $checkbox.filter('[readonly]').prev().addClass('readonly').closest('label').on("click mousedown", false);
        base.add('on_checkbox_change', function(data) {
            var co = $(data.target).data('connection');
            if (co) {
                $checkbox.filter('[name="' + co + '"]').prop('checked', data.target.checked).trigger("change");
            }
        });
    }

    if ($radio.length) {
        $radio.each(function() {
            if (!$(this).prev().is('.radio')) {
                $(this).before('<a class="radio' + (this.checked ? ' checked' : "") + '" href="#check"></a>');
            }
        }).on("change", function(e) {
            var data = {
                'event': e,
                'target': this
            };
            if ($(this).is('[disabled]') || $(this).is('[readonly]')) return false;
            $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked');
            $radio.filter('[name="' + this.name + '"]').not(this).prop('checked', false).prev().removeClass('checked');
            base.fire('on_radio_change', data);
            base.fire('on_radio_' + (this.checked ? 'check' : 'uncheck'), data);
        });
        $('.radio').on("click", function() {
            if ($(this).is('.disabled') || $(this).is('.readonly') || $(this).is('.checked')) return false;
            $(this).next().prop('checked', true).trigger("change");
            return false;
        }).on("mousedown", false);
        $radio.filter('[disabled]').prev().addClass('disabled').closest('label').on("click mousedown", false);
        $radio.filter('[readonly]').prev().addClass('readonly').closest('label').on("click mousedown", false);
    }

})(DASHBOARD, DASHBOARD.$);