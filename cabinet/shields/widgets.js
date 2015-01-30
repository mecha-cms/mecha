var Widget = {};


/**
 * Widget Archive
 * --------------
 */

Widget.archive = function(type, id) {

    // Widget Archive HIERARCHY
    if (type == 'HIERARCHY') {
        var elem = document.getElementById(id);
        if (!elem) return;
        var toggle = elem.getElementsByTagName('ul')[0].getElementsByTagName('a'),
            click = function(ref) {
                var arrow = ['&#9660;', '&#9658;'];
                ref.onclick = function() {
                    var s = / selected$/i.test(this.parentNode.className) ? ' selected' : "";
                    if (this.children[0].className == 'zippy toggle-close') {
                        this.children[0].className = 'zippy toggle-open';
                        this.children[0].innerHTML = arrow[0];
                        this.parentNode.className = 'archive-date expanded' + s;
                        this.parentNode.getElementsByTagName('ul')[0].className = 'expanded';
                    } else {
                        this.children[0].className = 'zippy toggle-close';
                        this.children[0].innerHTML = arrow[1];
                        this.parentNode.className = 'archive-date collapsed' + s;
                        this.parentNode.getElementsByTagName('ul')[0].className = 'collapsed';
                    }
                    return false;
                };
            };
        if (!toggle) return;
        for (var i = 0, toggles = toggle.length; i < toggles; ++i) {
            if (/(^| )toggle( |$)/.test(toggle[i].className)) click(toggle[i]);
        }
    }

    // Widget Archive DROPDOWN
    if (type == 'DROPDOWN') {
        elem = document.getElementById(id);
        if (!elem) return;
        var select = elem.getElementsByTagName('select')[0];
        if (!select) return;
        select.onchange = function() {
            window.location.href = this.value;
        };
    }

};


/**
 * Widget Tag
 * ----------
 */

Widget.tag = function(type, id) {

    // Widget Tag DROPDOWN
    if (type == 'DROPDOWN') {
        elem = document.getElementById(id);
        if (!elem) return;
        var select = elem.getElementsByTagName('select')[0];
        if (!select) return;
        select.onchange = function() {
            window.location.href = this.value;
        };
    }

};


/**
 * FIRE !!!
 * --------
 */

(function(d, w) {
    var elem = d.getElementsByTagName('div');
    for (var i = 0, len = elem.length; i < len; ++i) {
        if (/(^| )widget-archive widget-archive-hierarchy( |$)/.test(elem[i].className)) {
            w.archive('HIERARCHY', elem[i].id);
        }
        if (/(^| )widget-archive widget-archive-dropdown( |$)/.test(elem[i].className)) {
            w.archive('DROPDOWN', elem[i].id);
        }
        if (/(^| )widget-tag widget-tag-dropdown( |$)/.test(elem[i].className)) {
            w.tag('DROPDOWN', elem[i].id);
        }
    }
})(document, Widget);