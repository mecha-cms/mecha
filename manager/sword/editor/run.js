// Apply MTE to all `<textarea>` elements ...

(function(w, d, base) {
    if (typeof MTE == "undefined") return;
    base.add('on_ajax_success', function(data) {
        base.fire('on_preview_complete', data);
    });
    base.add('on_ajax_error', function(data) {
        base.fire('on_preview_failure', data);
    });
    var area = d.getElementsByTagName('textarea'),
        speak = base.languages.MTE;
    if (!area || !area.length) return;
    for (var i = 0, len = area.length; i < len; ++i) {
        var name = area[i].name.replace(/\[\]/g, '_' + i).replace(/\[(.*?)\]/, '_$1'),
            shortcut = area[i].getAttribute('data-mte-use-shortcut') || false,
            toolbar = area[i].getAttribute('data-mte-use-toolbar') || false,
            prefix = toolbar ? 'composer' : 'editor';
        base.fire('on_control_begin', {
            'segment': base.segment,
            'name': name,
            'index': i
        });
        base[prefix + '_' + name] = /(^| )(MTE|code)( |$)/.test(area[i].className) && !/(^| )MTE-ignore( |$)/.test(area[i].className) ? new MTE(area[i], {
            tabSize: TAB,
            toolbar: toolbar,
            shortcut: shortcut,
            toolbarClass: 'editor-toolbar cf',
            buttonClassPrefix: 'editor-toolbar-button editor-toolbar-button-',
            iconClassPrefix: 'fa fa-',
            emptyElementSuffix: ES,
            buttons: speak.buttons,
            prompt: speak.prompt,
            placeholder: speak.placeholder,
            click: function(e, editor, type) {
                base.fire('on_control_event_click', {
                    'event': e,
                    'editor': editor,
                    'id': type,
                    'index': i,
                    'info': {
                        'segment': base.segment,
                        'name': name
                    }
                });
            },
            keydown: function(e, editor) {
                base.fire('on_control_event_keydown', {
                    'event': e,
                    'editor': editor,
                    'index': i,
                    'info': {
                        'segment': base.segment,
                        'name': name
                    }
                });
            },
            ready: function(editor) {
                base.fire('on_control_event_ready', {
                    'editor': editor,
                    'index': i,
                    'info': {
                        'segment': base.segment,
                        'name': name,
                    }
                });
            }
        }) : {};
        if (i === 0) {
            base[prefix] = base[prefix + '_' + name];
        }
        base.fire('on_control_end', {
            'segment': base.segment,
            'name': name,
            'index': i
        });
    }
})(window, document, DASHBOARD);