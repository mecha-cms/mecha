/**
 * Responsive Tooltip
 * ------------------
 */

(function($) {

    var $window = $(window),
        $document = $(document),
        $body = $(document.body),
        $target = $('.help[title]'),
        $tooltip = $('<div class="tooltip t hidden"></div>').appendTo($body);

    if (!$target.length) return;

    $target.on("mouseenter", function() {

        if (!$(this).data('title')) return;

        var $this = $(this);

        $tooltip.removeAttr('style')
            .html($this.data('title') + '<span class="tooltip-arrow"></span>')
                .removeClass('t r b l hidden');


        /**
         * padding vertical = 6
         * padding horizontal = 8
         */

        var distance = {
                vertical: 6,
                horizontal: 8
            },
            width = $tooltip.width(),
            height = $tooltip.height(),
            pos = $this.offset(),
            top = pos.top - height - distance.vertical - 2,
            left = pos.left - (width / 2) + ($this.width() / 2);

        if (top - $window.scrollTop() <= 0) {
            top = pos.top + $this.height();
            $tooltip.removeClass('t').addClass('b');
        } else {
            $tooltip.removeClass('b').addClass('t');
        }

        if (left + width > $window.width()) {
            left = pos.left - width + $this.width() + distance.horizontal;
            $tooltip.removeClass('r').addClass('l');
        } else if (left <= 0) {
            left = pos.left - distance.horizontal;
            $tooltip.removeClass('l').addClass('r');
        }

        $tooltip.css({
            top: top,
            left: left
        });

    }).data('title', function() {
        return this.title ? this.title : false;
    }).removeAttr('title');

    $tooltip.on("mouseleave", function() {
        $(this).removeAttr('style').removeClass('t r b l').addClass('hidden');
    });

    $window.on("resize", function() {
        $tooltip.removeAttr('style').removeClass('t r b l').addClass('hidden');
    });

})(Zepto);