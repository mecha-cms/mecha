<?php

final class Guard extends Genome {

    public static function abort(string $alert, $exit = true) {
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = explode("\n", n(ob_get_clean()), 2);
        array_shift($trace);
        $trace = trim(str_replace(ROOT, '.', implode("\n", $trace)), "\n");

        echo <<<HTML
<details style="
  margin: 0;
  padding: 0;
  background: #f00;
  font: normal normal 100%/1.5 sans-serif;
  color: #fff;
  selection: none;
">
  <summary style="
    margin: 0;
    padding: .5em 1rem;
    display: block;
    cursor: pointer;
  ">$alert</summary>
  <pre style="
    margin: 0;
    padding: 0;
    background: #000;
    font: normal normal 100%/1.25 monospace;
    white-space: pre;
    overflow: auto;
  "><code style="
    margin: 0;
    padding: .5em 1rem;
    display: block;
    font: inherit;
  ">$trace</code></pre>
</details>
HTML;

        $exit && exit;
    }

    public static function check(string $token, $id = 0) {
        $prev = $_SESSION['token'][$id] ?? [0, ""];
        return $prev[1] && $token && $prev[1] === $token ? $token : false;
    }

    public static function hash(string $salt = "") {
        return sha1(uniqid(mt_rand(), true) . $salt);
    }

    public static function token($id = 0, $for = '1 minute') {
        $prev = $_SESSION['token'][$id] ?? [0, ""];
        if ($prev[0] > time()) {
            return $prev[1];
        }
        $t = is_string($for) ? strtotime($for) : time() + $for;
        $_SESSION['token'][$id] = $v = [$t, self::hash($id)];
        return $v[1];
    }

}
