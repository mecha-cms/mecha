/**
 * Table Row More-Less
 * -------------------
 */

(function($) {

    var $btn = $('.row-more-less .btn');

    $btn.on("click", function() {

        var clone = $(this).closest('tr').prev().clone(true),
            max = $(this).closest('tr').data('max'),
            min = $(this).closest('tr').data('min'),
            length = $(this).closest('tbody').find('tr').length,
            callback = $(this).closest('tr').attr('data-callback') ? $(this).closest('tr').data('callback') : false;

        if ($(this).is('.btn-more')) {
            if (length < max + 1) {
                $(this).closest('tr').before(clone);
            }
        } else {
            if (length > min + 1 && $(this).closest('tr').prev().find('input:not([type="hidden"])').val() === "") {
                $(this).closest('tr').prev().remove();
            }
        }

        if (callback) eval(callback); // :(

        return false;

    });

})(Zepto);