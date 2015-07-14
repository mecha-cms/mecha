/**
 * Tab
 * ---
 *
 *    <div class="tab-area">
 *      <a class="tab active" href="#tab-content-1">Tab 1</a>
 *      <a class="tab" href="#tab-content-2">Tab 2</a>
 *      <a class="tab" href="#tab-content-3">Tab 3</a>
 *    </div>
 *    <div class="tab-content-area">
 *      <div class="tab-content" id="tab-content-1">Test content 1.</div>
 *      <div class="tab-content hidden" id="tab-content-2">Test content 2.</div>
 *      <div class="tab-content hidden" id="tab-content-3">Test content 3.</div>
 *    </div>
 *
 */

(function($, base) {

    var $tab = $('.tab'), $panel;

    if (!$tab.length) return;

    $tab.on("click", function(e) {
        var data = {
            'event': e,
            'target': this
        };
        base.fire('before_tab_change', data);
        if (!this.href || this.href.match(/\#.*$/)) {
            var hash = (this.hash || "").replace('#', ""),
                active = $(this).is('.active'),
                toggle = $(this).hasClass('toggle');
            $panel = hash !== "" ? $('#' + hash) : [];
            if (!$panel.length) {
                $panel = $tab.parent().parent().find('.tab-content').eq($(this).index());
            }
            // NOTE: Force `addClass` for `.toggle` ...
            $(this).addClass('active').siblings().removeClass('active');
            $panel[toggle ? 'toggleClass' : 'removeClass']('hidden').siblings('.tab-content').addClass('hidden');
            base.fire('on_tab_change', data);
            if (toggle) base.fire('on_tab_toggle', data);
            base.fire('on_tab_' + (active ? 'hide' : 'show'), data);
        } else {
            if (this.href) {
                if ($(this).attr('data-confirm-text')) {
                    if (window.confirm($(this).data('confirmText'))) {
                        window.location.href = this.href;
                    }
                } else {
                    window.location.href = this.href;
                }
            }
        }
        return false;
    }).on("mousedown", false);

})(window.Zepto || window.jQuery, DASHBOARD);