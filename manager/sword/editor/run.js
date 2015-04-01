/**
 * Disabled HTML Parser
 * --------------------
 *
 * To keep the API remains semantic, start from
 * here you can call MTE as Mecha Text Editor.
 *
 */

if (typeof DASHBOARD !== "undefined") {
    var MTE = MTE || {};
    if (!DASHBOARD.is_html_parser_enabled) MTE = HTE;
}


/**
 * Run the MTE Plugin
 * ------------------
 *
 * Apply MTE to all `<textarea>` elements ...
 *
 */

(function(w, d, base) {
    if (typeof MTE === "undefined") return;
    function extend(a, b) {
        a = a || {};
        for (var c in b) {
            if (typeof b[c] === "object") {
                a[c] = extend(a[c], b[c]);
            } else {
                a[c] = b[c];
            }
        }
        return a;
    }
    base.add('on_ajax_success', function(data) {
        base.fire('on_preview_complete', data);
    });
    base.add('on_ajax_error', function(data) {
        base.fire('on_preview_failure', data);
    });
    var area = d.getElementsByTagName('textarea'),
        speak = base.languages.MTE;
    if (!area || !area.length) return;
    var c_na = "", c_nu = 0;
    for (var i = 0, len = area.length; i < len; ++i) {
        var name = area[i].name, hook, config, prefix;
        if (c_na !== name) {
            c_na = name;
            c_nu = 0;
        }
        // Replace `foo[]` with `foo_0`
        // Replace `foo[bar]` with `foo_bar`
        hook = name.replace(/\[\]/g, '_' + c_nu).replace(/\[(.*?)\]/g, '_$1');
        config = area[i].getAttribute('data-MTE-config') || '{}';
        config = typeof JSON.parse === "function" ? JSON.parse(config) : {};
        prefix = config.toolbar ? 'composer' : 'editor';
        base.fire('on_control_begin', {
            'index': i,
            'info': {
                'segment': base.segment,
                'name': name
            }
        });
        base[prefix + '_' + hook] = /(^| )(MTE|code)( |$)/.test(area[i].className) && !/(^| )MTE-ignore( |$)/.test(area[i].className) ? new MTE(area[i], extend({
            tabSize: TAB || '    ',
            toolbar: false,
            shortcut: false,
            areaClass: 'editor-area',
            toolbarClass: 'editor-toolbar cf',
            toolbarIconClass: 'fa fa-%s',
            toolbarButtonClass: 'editor-toolbar-button editor-toolbar-button-%s',
            toolbarSeparatorClass: 'editor-toolbar-separator',
            dropClass: 'custom-drop custom-%s-drop cf',
            modalClass: 'custom-modal custom-modal-%s',
            modalHeaderClass: 'custom-modal-header custom-modal-%s-header cf',
            modalContentClass: 'custom-modal-content custom-modal-%s-content cf',
            modalFooterClass: 'custom-modal-action custom-modal-%s-action cf',
            modalOverlayClass: 'custom-modal-overlay custom-modal-%s-overlay',
            emptyElementSuffix: ES || '>',
            PRE: base.is_html_parser_enabled ? '~~~\n%s\n~~~' : 'pre',
            buttons: speak.buttons,
            prompts: speak.prompts,
            placeholders: speak.placeholders,
            update: function(e, editor, id) {
                base.fire('on_control_event_update', {
                    'event': e,
                    'editor': editor,
                    'id': id || null,
                    'index': i,
                    'info': {
                        'segment': base.segment,
                        'name': name
                    }
                });
            },
            click: function(e, editor, id) {
                base.fire('on_control_event_click', {
                    'event': e,
                    'editor': editor,
                    'id': id,
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
                        'name': name
                    }
                });
            },
            copy: function(s) {
                base.fire('on_control_event_copy', {
                    'selection': s,
                    'index': i,
                    'info': {
                        'segment': base.segment,
                        'name': name
                    }
                });
            },
            cut: function(s) {
                base.fire('on_control_event_cut', {
                    'selection': s,
                    'index': i,
                    'info': {
                        'segment': base.segment,
                        'name': name
                    }
                });
            },
            paste: function(s) {
                base.fire('on_control_event_paste', {
                    'selection': s,
                    'index': i,
                    'info': {
                        'segment': base.segment,
                        'name': name
                    }
                });
            }
        }, config)) : {};
        if (i === 0 || /(^| )MTE-main( |$)/.test(area[i].className)) {
            base[prefix] = base[prefix + '_' + hook];
        }
        base.fire('on_control_end', {
            'index': i,
            'info': {
                'segment': base.segment,
                'name': name
            }
        });
        c_nu++;
    }
})(window, document, DASHBOARD);