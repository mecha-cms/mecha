/**
 * Article/Page Editor
 * -------------------
 */

(function($) {

    var $zone = $(document.body),
        $editor = $('.form-compose').first(),
        $preview = $('.editor-preview'),
        $title = $('[name="title"]', $editor),
        $slug = $('[name="slug"]', $editor),
        $content = $('[name="content"]', $editor),
        $tab = $('.tab-area a'),
        $check = $('input[type="checkbox"]', $editor),
        $css = $('[name="css"]', $editor),
        $javascript = $('[name="js"]', $editor),
        $cssCheck = $('[name="css_live_check"]', $editor),
        $javascriptCheck = $('[name="js_live_check"]', $editor);

    $zone.removeClass('no-js').addClass('js');

    var $cssPreview = $('<div id="live-preview-css"></div>').appendTo($zone);
    var $javascriptPreview = $('<div id="live-preview-js"></div>').appendTo($zone);

    $tab.on("click", function() {
        if (this.hash.replace('#', "") == 'tab-content-4') { // preview tab only
            $preview.html($preview.data('progressText'));
            $.ajax({
                url: $editor.data('previewUrl'),
                type: 'POST',
                data: $editor.serializeArray(),
                success: function(data, textStatus, jqXHR) {
                    $preview.html(data);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $preview.html($preview.data('errorText'));
                }
            });
        }
        return false;
    });

    if ($content.length) {
        var mte = new MTE($content[0], {
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
        new MTE($css[0], {
            tabSize: '  ',
            toolbar: false
        });
        new MTE($javascript[0], {
            tabSize: '    ',
            toolbar: false
        });
    }

    if ($('.btn-delete').length === 0) {
        $.slugger($title, $slug, '-');
    }

    $css.on("keyup", function() {
        setTimeout(function() {
            if ($cssCheck.is(':checked')) {
                $cssPreview.html($css.val());
            }
        }, 15);
    });

    $javascript.on("keyup", function() {
        setTimeout(function() {
            if ($javascriptCheck.is(':checked')) {
                $javascriptPreview.html($javascript.val());
            }
        }, 15);
    });

    $cssCheck.add($javascriptCheck).on("change", function() {
        if (this.checked) {
            $css.add($javascript).trigger("keyup");
        } else {
            $cssPreview.add($javascriptPreview).html("");
        }
    });

})(Zepto);