/**
 * Sortable Table Rows
 * -------------------
 *
 *    <table class="table-sortable">
 *      <thead>
 *        <tr>
 *          <th>Test 1</th>
 *          <th>Test 2</th>
 *        </tr>
 *      </thead>
 *      <tbody>
 *        <tr>
 *          <td class="text-center align-middle">
 *            <a class="sort" href="#move-up">&uarr;</a>
 *            <a class="sort" href="#move-down">&darr;</a>
 *          </td>
 *          <td>Test item content 1.</td>
 *        </tr>
 *        <tr>
 *          <td class="text-center align-middle">
 *            <a class="sort" href="#move-up">&uarr;</a>
 *            <a class="sort" href="#move-down">&darr;</a>
 *          </td>
 *          <td>Test item content 2.</td>
 *        </tr>
 *      </tbody>
 *    </table>
 *
 */

(function($, base) {

    var $tbody = $('.table-sortable tbody');

    if (!$tbody) return;

    $tbody.on("click", 'td > .sort', function(e) {
        var $tr = $(this).closest('tr');
        $tr.addClass('active').siblings().removeClass('active');
        if (this.hash.replace('#', "") == 'move-up') {
            if ($tr.prev().is('tr')) {
                $tr.insertBefore($tr.prev());
                base.fire('on_row_move_up', [e, this]);
            }
        } else {
            if ($tr.next().is('tr')) {
                $tr.insertAfter($tr.next());
                base.fire('on_row_move_down', [e, this]);
            }
        }
        base.fire('on_row_move', [e, this]);
        base.fire('on_row_sort', [e, this]);
        return false;
    }).find('tr').on("click", function() {
        $(this).siblings().removeClass('active');
    });

})(Zepto, DASHBOARD);


/**
 * Sortable Items
 * --------------
 *
 *    <div class="sortable-area">
 *      <div class="sortable">
 *        <p>Test content.</p>
 *        <span class="handle">
 *          <a class="sort" href="#move-up">&uarr;</a>
 *          <a class="sort" href="#move-down">&darr;</a>
 *        </span>
 *      </div>
 *    </div>
 *
 */

(function($, base) {

    var $sortable = $('.sortable'),
        $base = $sortable.parent();

    if (!$sortable) return;

    $sortable.on("click", '.sort', function(e) {
        var $elem = $(this).closest('.sortable');
        $elem.addClass('active').siblings().removeClass('active');
        if (this.hash.replace('#', "") == 'move-up') {
            if ($elem.prev().is('.sortable')) {
                $elem.insertBefore($elem.prev());
                base.fire('on_item_move_up', [e, this]);
            }
        } else {
            if ($elem.next().is('.sortable')) {
                $elem.insertAfter($elem.next());
                base.fire('on_item_move_down', [e, this]);
            }
        }
        base.fire('on_item_move', [e, this]);
        base.fire('on_item_sort', [e, this]);
        return false;
    }).not('.active').on("click", function() {
        $(this).siblings().removeClass('active');
    });

})(Zepto, DASHBOARD);