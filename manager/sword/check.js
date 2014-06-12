/**
 * Custom Radio and Checkbox
 * -------------------------
 */

(function($) {

    var $checkbox = $('input[type="checkbox"]'),
        $radio = $('input[type="radio"]');

    if (!$checkbox.length && !$radio.length) return;

    $radio.each(function() {
        $(this).before('<a class="radio' + (this.checked ? ' checked' : "") + '" href="#"><i class="fa fa-' + (this.checked ? 'check-' : "") + 'circle"></i></a>');
    }).on("change", function() {
        $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked').html('<i class="fa fa-' + (this.checked ? 'check-' : "") + 'circle"></i>');
    });

    $checkbox.each(function() {
        $(this).before('<a class="checkbox' + (this.checked ? ' checked' : "") + '" href="#"><i class="fa fa-' + (this.checked ? 'check-' : "") + 'square"></i></a>');
    }).on("change", function() {
        $(this).prev()[this.checked ? 'addClass' : 'removeClass']('checked').html('<i class="fa fa-' + (this.checked ? 'check-' : "") + 'square"></i>');
    });

    $('.radio').on("click", function() {
        if ($(this).is('.checked')) return false;
        $(this).addClass('checked').html('<i class="fa fa-check-circle"></i>').siblings().removeClass('checked').html('<i class="fa fa-circle"></i>');
        $(this).next().prop('checked', true).trigger("change").siblings('[name="' + $(this).next().attr('name') + '"]').prop('checked', false).trigger("change");
        return false;
    }).on("mousedown", function() {
        return false;
    });

    $('.checkbox').on("click", function() {
        $(this).toggleClass('checked').html('<i class="fa fa-' + ($(this).is('.checked') ? 'check-' : "") + 'square"></i>').next().prop('checked', $(this).is('.checked')).trigger("change");
        return false;
    }).on("mousedown", function() {
        return false;
    });

    $radio.addClass('hidden');
    $checkbox.addClass('hidden');

})(Zepto);