/**
 * Modal
 * -----
 *
 *    <div class="modal" data-trigger="#my-button">
 *      <h3 class="modal-header">Modal Title</h3>
 *      <a class="modal-close-x" href="#">&times;</a>
 *      <div class="modal-content">
 *        <div class="modal-content-inner">
 *          <p>Test content.</p>
 *        </div>
 *      </div>
 *      <div class="modal-footer">
 *        <button class="btn btn-default modal-close">Close Modal</button>
 *      </div>
 *    </div>
 *
 */

(function($, base) {

    var $body = $(document.body),
        $modal = $('.modal'),
        $close = $('.modal-close, .modal-close-x');

    if (!$modal.length) return;

    $close.on("click", function(e) {
        $(this).closest('.modal').hide().prev().hide();
        $body.css('overflow', "").parent().css('overflow', "");
        base.fire('on_modal_hide', {
            'event': e,
            'target': this
        });
        return false;
    });

    $modal.each(function() {
        var $this = $(this),
            $trigger = $this.data('trigger') || false;
        $('<div class="modal-overlay"></div>').css('z-index', $this.css('z-index')).on("click", function(e) {
            $(this).hide().next().hide();
            $body.css('overflow', "").parent().css('overflow', "");
            base.fire('on_modal_hide', {
                'event': e,
                'target': this
            });
        }).insertBefore($this);
        if ($trigger) {
            $body.on("click", $trigger, function(e) {
                $this.show().prev().show();
                if ($this.hasClass('modal-full-screen')) {
                    $body.css('overflow', 'hidden').parent().css('overflow', 'hidden');
                }
                base.fire('on_modal_show', {
                    'event': e,
                    'target': this
                });
                return false;
            });
        }
    });

})(window.Zepto || window.jQuery, DASHBOARD);