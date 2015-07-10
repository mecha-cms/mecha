/**
 * Accordion
 * ---------
 *
 *    <div class="accordion-area">
 *      <h4 class="accordion-header active">Test Header 1</h4>
 *      <div class="accordion-content">Test content 1.</div>
 *      <h4 class="accordion-header">Test Header 2</h4>
 *      <div class="accordion-content hidden">Test content 2.</div>
 *      <h4 class="accordion-header">Test Header 3</h4>
 *      <div class="accordion-content hidden">Test content 3.</div>
 *    </div>
 *
 */

(function($, base) {

    var $accordion = $('.accordion-area');

    if (!$accordion.length) return;

    $accordion.find('.accordion-header').on("click", function(e) {
        var active = $(this).is('.active'),
            data = {
                'event': e,
                'target': this
            };
        $(this).toggleClass('active').siblings('.accordion-header').removeClass('active');
        $(this).next().toggleClass('hidden').siblings('.accordion-content').addClass('hidden');
        base.fire('on_accordion_change', data);
        base.fire('on_accordion_toggle', data);
        base.fire('on_accordion_' + (active ? 'collapse' : 'expand'), data);
        return false;
    }).on("mousedown", false);

})(window.Zepto || window.jQuery, DASHBOARD);