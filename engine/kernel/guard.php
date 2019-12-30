<?php

final class Guard extends Genome {

    public static function abort(string $alert, $exit = true) {
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = explode("\n", n(ob_get_clean()), 2);
        array_shift($trace);
        $trace = str_replace(ROOT, '.', implode("\n", $trace));
        echo '<details style="margin:0;padding:0;background:#f00;font:normal normal 13px/1.5 sans-serif;color:#fff;selection:none;">';
        echo '<summary style="margin:0;padding:.5em 1em;display:block;cursor:help;">' . $alert . '</summary>';
        echo '<pre style="margin:0;padding:0;background:#000;font:normal normal 100%/1.25 monospace;white-space:pre;overflow:auto;"><code style="margin:0;padding:.5em 1em;display:block;font:inherit;">' . $trace . '</code></pre>';
        echo '</details>';
        $exit && exit;
    }

    public static function check(string $token, $id = 0) {
        $prev = $_SESSION['token'][$id] ?? "";
        return $prev && $token && $prev === $token ? $token : false;
    }

    public static function hash(string $salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function kick(string $path = null) {
        $path = $path ?? $GLOBALS['url']->current;
        header('Location: ' . URL::long($path, false));
        exit;
    }

    public static function token($id = 0) {
        return ($_SESSION['token'][$id] = self::hash($id));
    }

}