/**
 * Table Row More-Less
 * -------------------
 *
 *    <tr class="row-more-less" data-min="3" data-max="9999">
 *      <td>
 *        <a class="btn btn-sm btn-default btn-increase" href="#add">More</a>
 *        <a class="btn btn-sm btn-default btn-decrease" href="#remove">Less</a>
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

        if ($(this).is('.btn-increase')) {
            if (length < max + 1) {
                $(this).closest('tr').before(clone);
                base.fire('on_row_increase', {
                    'event': e,
                    'target': this
                });
            }
        } else {
            if (length > min + 1) {
                $(this).closest('tr').prev().remove();
                base.fire('on_row_decrease', {
                    'event': e,
                    'target': this
                });
            }
        }

        base.fire('on_row_update', {
            'event': e,
            'target': this
        });

        return false;

    });

})(Zepto, DASHBOARD);