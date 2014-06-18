/**
 * Table Row More-Less
 * -------------------
 *
 *    <tr class="row-more-less" data-min="3" data-max="9999">
 *      <td>
 *        <a class="btn btn-sm btn-more" href="#add">More</a>
 *        <a class="btn btn-sm btn-less" href="#remove">Less</a>
 *      </td>
 *    </tr>
 *
 */

(function($, base) {

    var $btn = $('.row-more-less .btn');

    if (!$btn.length) return;

    $btn.on("click", function(e) {

        var clone = $(this).closest('tr').prev().clone(true),
            max = $(this).closest('tr').data('max'),
            min = $(this).closest('tr').data('min'),
            length = $(this).closest('tbody').find('tr').length;

        if ($(this).is('.btn-more')) {
            if (length < max + 1) {
                $(this).closest('tr').before(clone);
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