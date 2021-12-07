<?php

namespace x {
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
    \Hook::set([
        'page.content'
    ], __NAMESPACE__ . "\\markdown", 2);
}

namespace x\markdown {
    function span($content) { // Inline tag(s) only
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
    ], __NAMESPACE__ . "\\span", 2);
}