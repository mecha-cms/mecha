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

(function($) {

    $accordion = $('.accordion-area');

    if (!$accordion.length) return;

    $accordion.find('.accordion-header').on("click", function() {
        var active = $(this).is('.active');
        $(this).toggleClass('active').siblings('.accordion-header').removeClass('active');
        $(this).next().toggleClass('hidden').siblings('.accordion-content').addClass('hidden');
        $(this).find('.fa').removeClass('fa-' + (active ? 'minus' : 'plus') + '-square').addClass('fa-' + (active ? 'plus' : 'minus') + '-square').closest('.accordion-header').siblings().find('.fa').removeClass('fa-minus-square').addClass('fa-plus-square');
        return false;
    }).on("mousedown", function() {
        return false;
    });

    $accordion.find('.accordion-header:not(.active)').prepend('<i class="fa fa-fw fa-plus-square"></i>');
    $accordion.find('.accordion-header.active').prepend('<i class="fa fa-fw fa-minus-square"></i>');

})(Zepto);