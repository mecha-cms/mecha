(function(win, doc) {

    var W = function() {

        var base = this, $;
    
        base.el = function(em) {
            return typeof em === "string" ? doc.getElementById(em) : em;
        };

        base.archive = function(type, id) {
            $ = base.el(id);
            if (!$) return;
            if (type === 'HIERARCHY') {
                var ul = $.getElementsByTagName('ul'),
                    click = function(r) {
                        r.onclick = function() {
                            var parent = this.parentNode,
                                s = /\scurrent$/i.test(parent.className) ? ' current' : "",
                                close = this.className === 'toggle close';
                            this.className = 'toggle ' + (close ? 'open' : 'close');
                            parent.className = (close ? 'open' : 'close') + s;
                            return false;
                        };
                    };
                if (!ul.length) return;
                var a = ul[0].getElementsByTagName('a');
                if (!a.length) return;
                for (var i = 0, as = a.length; i < as; ++i) {
                    if (/(^|\s)toggle(\s|$)/.test(a[i].className)) click(a[i]);
                }
            }
            if (type === 'DROPDOWN') {
                var select = $.getElementsByTagName('select');
                if (!select.length) return;
                select[0].onchange = function() {
                    win.location.href = this.value;
                };
            }
        };

        base.tag = function(type, id) {
            $ = base.el(id);
            if (!$) return;
            if (type === 'DROPDOWN') {
                var select = $.getElementsByTagName('select');
                if (!select.length) return;
                select[0].onchange = function() {
                    win.location.href = this.value;
                };
            }
        };

    };

    // plug ...
    win.Widget = new W();

    // and run ...
    var $ = doc.getElementsByTagName('div');
    for (var i = 0, len = $.length; i < len; ++i) {
        var cl = $[i].className;
        if (/(^|\s)widget-archive-hierarchy(\s|$)/.test(cl)) {
            Widget.archive('HIERARCHY', $[i]);
        } else if (/(^|\s)widget-archive-dropdown(\s|$)/.test(cl)) {
            Widget.archive('DROPDOWN', $[i]);
        } else if (/(^|\s)widget-tag-dropdown(\s|$)/.test(cl)) {
            Widget.tag('DROPDOWN', $[i]);
        }
    }

})(window, document);