/**
 * Tabs
 * ----
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

(function($) {

    var $tabs = $('.tab-area a');

    $tabs.on("click", function() {
        if (this.href.match(/\#.*$/)) {
            $(this).addClass('active').siblings().removeClass('active');
            $('#' + this.hash.replace('#', "")).removeClass('hidden').siblings('.tab-content').addClass('hidden');
        } else {
            if ($(this).attr('data-confirm-text')) {
                if (window.confirm($(this).data('confirmText'))) {
                    window.location.href = this.href;
                }
            } else {
                window.location.href = this.href;
            }
        }
        return false;
    });

})(Zepto);