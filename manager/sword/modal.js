/**
 * Modal
 * -----
 *
 *    <div class="modal" data-trigger="#my-button">
 *      <h3 class="modal-header">Modal Title</h3>
 *      <a href="#" class="modal-close-x">&times;</a>
 *      <div class="modal-content">
 *        <div class="modal-content-inner">
 *          <p>Test content.</p>
 *        </div>
 *      </div>
 *      <div class="modal-footer">
 *        <button class="btn modal-close">Close Modal</button>
 *      </div>
 *    </div>
 *
 */

(function($) {

    var $body = $(document.body),
        $modal = $('.modal'),
        $close = $('.modal-close, .modal-close-x');

    if (!$modal.length) return;

    $close.on("click", function() {
        $(this).closest('.modal').hide().prev().hide();
        return false;
    });

    $modal.each(function() {
        var $this = $(this);
        $('<div class="modal-overlay"></div>').css('z-index', $this.css('z-index')).insertBefore($this);
        var $trigger = $this.attr('data-trigger') ? $this.data('trigger') : false;
        if ($trigger) {
            $body.on("click", $trigger, function() {
                $this.show().prev().show();
                return false;
            });
        }
    });

})(Zepto);