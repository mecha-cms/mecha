/**
 * Table Row More-Less
 * -------------------
 *
 *    <tr class="row-more-less" data-min="3" data-max="9999">
 *      <td>
 *        <a class="row-more" href="#row:more">More</a>
 *        <a class="row-less" href="#row:less">Less</a>
 *      </td>
 *    </tr>
 *
 */

(function(base, $) {

    var $row = $('.row-more-less');

    if (!$row.length) return;

    $row.on("click", 'a', function(e) {

        var clone = $(this).closest('tr').prev().clone(true),
            state = (this.hash || ':').replace('#', "").split(/[:\-]/)[1],
            max = $(this).closest('tr').data('max') || 9999,
            min = $(this).closest('tr').data('min') || 1,
            length = $(this).closest('tbody').find('tr').length,
            data = {
                'event': e,
                'target': this
            };

        if ($(this).is('.row-more') || state === 'more') {
            if (length < max + 1) {
                $(this).closest('tr').before(clone);
                base.fire('on_row_increase', data);
                base.fire('on_row_more', data);
                base.fire('on_row_update', data);
            }
            return false;
        }

        if ($(this).is('.row-less') || state === 'less') {
            if (length > min + 1) {
                $(this).closest('tr').prev().remove();
                base.fire('on_row_decrease', data);
                base.fire('on_row_less', data);
                base.fire('on_row_update', data);
            }
            return false;
        }

    });

})(DASHBOARD, DASHBOARD.$);