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
 *          <td class="handle">
 *            <a class="sort sort-up" href="#sort:up">&uarr;</a>
 *            <a class="sort sort-down" href="#sort:down">&darr;</a>
 *          </td>
 *          <td>Test item content 1.</td>
 *        </tr>
 *        <tr>
 *          <td class="handle">
 *            <a class="sort sort-up" href="#sort:up">&uarr;</a>
 *            <a class="sort sort-down" href="#sort:down">&darr;</a>
 *          </td>
 *          <td>Test item content 2.</td>
 *        </tr>
 *      </tbody>
 *    </table>
 *
 */

(function($, base) {

    var $tbody = $('.table-sortable tbody'),
        selected = null;

    if (!$tbody) return;

    $tbody.on("click", '.handle > a, td > .sort', function(e) {
        var $tr = $(this).closest('tr'),
            state = (this.hash || ':').replace('#', "").split(/[:\-]/)[1],
            data = {
                'event': e,
                'target': this
            };
        $tr.addClass('active').siblings().removeClass('active');
        if ($(this).is('.sort-up') || state == 'up') {
            if ($tr.prev().is('tr')) {
                $tr.insertBefore($tr.prev());
                base.fire('on_row_move_up', data);
                base.fire('on_row_sort_up', data);
            }
        } else {
            if ($tr.next().is('tr') && !$tr.next().is('.row-more-less')) {
                $tr.insertAfter($tr.next());
                base.fire('on_row_move_down', data);
                base.fire('on_row_sort_down', data);
            }
        }
        base.fire('on_row_move', data);
        base.fire('on_row_sort', data);
        return false;
    }).find('tr').on("click", function() {
        $(this).siblings().removeClass('active');
    });

    $tbody.find('.handle').each(function() {
        if (!$(this).find('a').length) {
            $(this).append('<a class="sort sort-up" href="#sort:up"><i class="fa fa-angle-up"></i></a><a class="sort sort-down" href="#sort:down"><i class="fa fa-angle-down"></i></a>');
        }
    }).parent().attr('draggable', true).on("dragstart", function(e) {
        selected = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text', this.innerHTML);
        $(this).addClass('origin');
    }).on("dragover", function(e) {
        e.preventDefault();
        $(this).addClass('target');
    }).on("dragleave", function() {
        $(this).removeClass('target');
    }).on("drop", function(e) {
        e.preventDefault();
        if (selected !== this) {
            var center = $(this).offset().top + ($(this).height() / 2);
            $(selected)[e.pageY >= center ? 'insertAfter' : 'insertBefore']($(this));
        }
    }).on("dragend", function() {
        $(this).removeClass('origin target').addClass('active').siblings().removeClass('active origin target');
    }).on("mouseover mouseout", 'button, input, select, textarea', function(e) {
        $(this).closest('[draggable]').attr('draggable', e.type === "mouseout");
    });

})(window.Zepto || window.jQuery, DASHBOARD);


/**
 * Sortable Items
 * --------------
 *
 *    <div class="sortable-area">
 *      <div class="sortable">
 *        <p>Test content.</p>
 *        <span class="handle">
 *          <a class="sort sort-up" href="#sort:up">&uarr;</a>
 *          <a class="sort sort-down" href="#sort:down">&darr;</a>
 *        </span>
 *      </div>
 *    </div>
 *
 */

(function($, base) {

    var $sortable = $('.sortable'),
        $base = $sortable.parent(),
        selected = null;

    if (!$sortable) return;

    $sortable.on("click", '.handle > a', function(e) {
        var $elem = $(this).closest('.sortable'),
            state = (this.hash || ':').replace('#', "").split(/[:\-]/)[1],
            data = {
                'event': e,
                'target': this
            };
        $elem.addClass('active').siblings().removeClass('active');
        if ($(this).is('.sort-up') || state == 'up') {
            if ($elem.prev().is('.sortable')) {
                $elem.insertBefore($elem.prev());
                base.fire('on_item_move_up', data);
                base.fire('on_item_sort_up', data);
            }
        } else {
            if ($elem.next().is('.sortable')) {
                $elem.insertAfter($elem.next());
                base.fire('on_item_move_down', data);
                base.fire('on_item_sort_down', data);
            }
        }
        base.fire('on_item_move', data);
        base.fire('on_item_sort', data);
        return false;
    }).not('.active').on("click", function() {
        $(this).siblings().removeClass('active');
    });

    $sortable.find('.handle').each(function() {
        if (!$(this).find('a').length) {
            $(this).append('<a class="sort sort-up" href="#sort:up"><i class="fa fa-angle-up"></i></a><a class="sort sort-down" href="#sort:down"><i class="fa fa-angle-down"></i></a>');
        }
    }).parent().attr('draggable', true).on("dragstart", function(e) {
        selected = this;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text', this.innerHTML);
        $(this).addClass('origin');
    }).on("dragover", function(e) {
        e.preventDefault();
        $(this).addClass('target');
    }).on("dragleave", function() {
        $(this).removeClass('target');
    }).on("drop", function(e) {
        e.preventDefault();
        if (selected !== this) {
            var center = $(this).offset().top + ($(this).height() / 2);
            $(selected)[e.pageY >= center ? 'insertAfter' : 'insertBefore']($(this));
        }
    }).on("dragend", function() {
        $(this).removeClass('origin target').addClass('active').siblings().removeClass('active origin target');
    }).on("mouseover mouseout", 'button, input, select, textarea', function(e) {
        $(this).closest('[draggable]').attr('draggable', e.type === "mouseout");
    });

})(window.Zepto || window.jQuery, DASHBOARD);