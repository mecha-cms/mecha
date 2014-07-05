var Widget = {};

Widget.archive = function(type, id) {

    // Widget Archive HIERARCHY
    if (type == 'HIERARCHY') {
        var elem = document.getElementById('widget-archive-hierarchy-' + id);
        if (!elem) return;
        var toggle = elem.getElementsByTagName('ul')[0].getElementsByTagName('a'),
            click = function(ref) {
                var arrow = ['&#9660;', '&#9658;'];
                ref.onclick = function() {
                    var s = / selected$/i.test(this.parentNode.className) ? ' selected' : "";
                    if (this.children[0].className == 'zippy') {
                        this.children[0].className = 'zippy toggle-open';
                        this.children[0].innerHTML = arrow[0];
                        this.parentNode.className = 'archive-date expanded' + s;
                        this.parentNode.getElementsByTagName('ul')[0].className = 'expanded';
                    } else {
                        this.children[0].className = 'zippy';
                        this.children[0].innerHTML = arrow[1];
                        this.parentNode.className = 'archive-date collapsed' + s;
                        this.parentNode.getElementsByTagName('ul')[0].className = 'collapsed';
                    }
                    return false;
                };
            };
        for (var i = 0, toggles = toggle.length; i < toggles; ++i) {
            if (/(^| )toggle( |$)/.test(toggle[i].className)) click(toggle[i]);
        }
    }

    // Widget Archive DROPDOWN
    if (type == 'DROPDOWN') {
        elem = document.getElementById('widget-archive-dropdown-' + id);
        if (!elem) return;
        var select = elem.getElementsByTagName('select')[0];
        select.onchange = function() {
            window.location.href = this.value;
        };
    }

};