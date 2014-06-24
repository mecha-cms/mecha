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

    var $accordion = $('.accordion-area'),
        $zone = $('html, body');

    if (!$accordion.length) return;

    $accordion.find('.accordion-header').on("click", function(e) {
        var active = $(this).is('.active');
        $(this).toggleClass('active').siblings('.accordion-header').removeClass('active');
        $(this).next().toggleClass('hidden').siblings('.accordion-content').addClass('hidden');
        if (!active) $zone.scrollTop($(this).offset().top);
        base.fire('on_accordion_change', [e, this]);
        base.fire('on_accordion_' + (active ? 'collapse' : 'expand'), [e, this]);
        return false;
    }).on("mousedown", function() {
        return false;
    });

})(Zepto, DASHBOARD);