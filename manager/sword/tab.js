/**
 * Tabs
 * ----
 */

(function($) {

    var $tabs = $('.tab-area a');

    $tabs.on("click", function() {
        if (this.href.match(/\#.*$/)) {
            $(this).addClass('active').siblings().removeClass('active');
            $('#' + this.hash.replace('#', "")).show().siblings('.tab-content').hide();
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