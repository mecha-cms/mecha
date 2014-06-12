/**
 * Table Row More-Less for Tag Manager
 * -----------------------------------
 */

(function($) {

    var $btn = $('.tag-row-more-less .btn');

    if (!$btn.length) return;

    function callback() {
        $('input[name="name[]"]').each(function() {
            $.slugger($(this), $(this).parent().next().find('input'), '-');
        });
    }

    $btn.on("click", function() {

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
            }
        } else {
            if (length > min + 1 && $(this).closest('tr').prev().find('input:not([type="hidden"])').val() === "") {
                $(this).closest('tr').prev().remove();
            }
        }

        callback();

        return false;

    });

    callback();

})(Zepto);