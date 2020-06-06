<?php

namespace _\lot\x\markdown {
    function inline($content) { // Inline tag(s) only
        $type = $this->type;
        if ('Markdown' !== $type && 'text/markdown' !== $type) {
            return $content;
        }
        $parser = new \ParsedownExtraPlugin;
        foreach (\State::get('x.markdown', true) ?? [] as $k => $v) {
            if (0 === \strpos($k, 'block')) {
                continue;
            }
            $parser->{$k} = $v;
        }
        return $parser->line($content ?? "");
    }
    \Hook::set([
        'page.description',
        'page.title'
    ], __NAMESPACE__ . "\\inline", 2);
}

namespace _\lot\x {
    function markdown($content) {
        $type = $this->type;
        if ('Markdown' !== $type && 'text/markdown' !== $type) {
            return $content;
        }
        $parser = new \ParsedownExtraPlugin;
        foreach (\State::get('x.markdown', true) ?? [] as $k => $v) {
            $parser->{$k} = $v;
        }
        return $parser->text($content ?? "");
    }
    // Add `text/markdown` to the file type list
    \File::$state['type']['text/markdown'] = 1;
    // Add `markdown` to the file extension list
    \File::$state['x']['markdown'] = 1;
    \File::$state['x']['md'] = 1; // Alias
    \File::$state['x']['mkd'] = 1; // Alias
    \Hook::set([
        'page.content'
    ], __NAMESPACE__ . "\\markdown", 2);
}
