(function($) {

    var body = $(document.body),
        form = $('.form-compose').first(),
        title = $('[name="title"]', form),
        slug = $('[name="slug"]', form),
        editor = $('[name="content"]', form),
        preview = $('.editor-preview'),
        tab = $('.tab-area a'),
        check = $('input[type="checkbox"]', form),
        css = $('[name="css"]', form),
        js = $('[name="js"]', form),
        css_check = $('[name="css_live_check"]', form),
        js_check = $('[name="js_live_check"]', form);

    body.removeClass('no-js').addClass('js');

    var css_preview = $('<div id="live-preview-css"></div>').appendTo(body);
    var js_preview = $('<div id="live-preview-js"></div>').appendTo(body);

    tab.on("click", function() {
        var shortcode = editor.data('shortcodes'),
            toHTML = editor.val();
        for (var i in shortcode) {
            var pattern = i.replace(/\%s/g, '(.*?)'),
                replace = shortcode[i].replace(/\\([0-9]+)/g, '$$1');
            toHTML = toHTML.replace(new RegExp('(?!`)' + pattern + '(?!`)', 'g'), replace).replace(/`\{\{(.*?)\}\}`/g, '{{$1}}');
        }
        preview.html(
            '<div class="inner"><h1 class="preview-title">' +
            (title.val().length ? title.val() : '&nbsp;') +
            '</h1><div class="p">' +
            Markdown(toHTML)
                .replace(/<table>/gi, '<table border="1">')
                .replace(/<t(d|h) align="(.*?)">/gi, '<t$1 style="text-align:$2;">') +
            '</div></div>'
        );
        return false;
    });

    if (editor.length) {
        var mte = new MTE(editor[0], {
            tabSize: '    ',
            toolbarClass: 'editor-toolbar',
            toolbars: {
                'table': {
                    'title': 'Table',
                    'position': 8,
                    'click': function() {
                        var s = mte.editor.selection(),
                            table = 'Table Header 1 | Table Header 2\n' +
                                    '-------------- | --------------\n' +
                                    'Table Cell 1.1 | Table Cell 1.2\n' +
                                    'Table Cell 2.1 | Table Cell 2.2';
                        mte.editor.insert(table, function() {
                            mte.editor.select(s.start, s.start + 14);
                            mte.editor.updateHistory();
                        });
                    }
                }
            },
            buttons: {
                OK: 'OK',
                CANCEL: 'Cancel'
            },
            prompt: {
                linkTitle: 'Your link title goes here...',
                linkTitle_title: 'Link Title',
                linkURL: 'http://',
                linkURL_title: 'Link URL',
                imageURL: 'http://',
                imageURL_title: 'Image URL'
            },
            placeholder: {
                headingText:'Heading',
                linkText: 'Your link text goes here...',
                imageAlt: 'Image',
                listUL: 'List Item',
                listOL: 'List Item'
            }
        });
        new MTE($('[name="css"]', form)[0], {
            tabSize: '  ',
            toolbar: false
        });
        new MTE($('[name="js"]', form)[0], {
            tabSize: '    ',
            toolbar: false
        });
    }

    $.slugger(title, slug, '-');

    css.on("keyup", function() {
        setTimeout(function() {
            if (css_check.is(':checked')) {
                css_preview.html(css.val());
            }
        }, 15);
    });

    js.on("keyup", function() {
        setTimeout(function() {
            if (js_check.is(':checked')) {
                js_preview.html(js.val());
            }
        }, 15);
    });

    css_check.add(js_check).on("change", function() {
        if (this.checked) {
            css.add(js).trigger("keyup");
        } else {
            css_preview.add(js_preview).html("");
        }
    });

})(Zepto);