/**
 * Table Row More-Less for Tag Manager
 * -----------------------------------
 */

(function($, base) {

    var $btn = $('.tag-row-more-less .btn');

    if (!$btn.length) return;

    $btn.on("click", function(e) {

        var clone = '<tr>' +
                '<td class="text-right"><input name="id[]" type="hidden" value="%s">%s</td>' +
                '<td><input name="name[]" type="text" class="input-block"></td>' +
                '<td><input name="slug[]" type="text" class="input-block"></td>' +
                '<td><input name="description[]" type="text" class="input-block"></td>' +
            '</tr>',
            max = $(this).closest('tr').data('max'),
            min = $(this).closest('tr').data('min'),
            length = $(this).closest('tbody').find('tr').length,
            id = parseInt($(this).closest('tr').prev().find('input[type="hidden"]').val(), 10);

        if ($(this).is('.btn-more')) {
            if (length < max + 1) {
                $(this).closest('tr').before(clone.replace(/%s/g, id + 1));
                base.fire('on_row_increase', [e, this]);
            }
        } else {
            if (length > min + 1 && $(this).closest('tr').prev().find('input:not([type="hidden"])').val() === "") {
                $(this).closest('tr').prev().remove();
                base.fire('on_row_decrease', [e, this]);
            }
        }

        base.fire('on_row_update', [e, this]);

        return false;

    });

})(Zepto, DASHBOARD);