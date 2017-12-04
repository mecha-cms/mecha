<?php

class Union extends Genome {

    const config = [
        'union' => [
            // 0 => [
            //     0 => ['\<', '\>', '\/'],
            //     1 => ['\=', '\"', '\"', '\s'],
            //     2 => ['\<\!\-\-', '\-\-\>']
            // ],
            1 => [
                0 => ['<', '>', '/', '[\w:.-]+'],
                1 => ['=', '"', '"', ' ', '[\w:.-]+'],
                2 => ['<!--', '-->']
            ]
        ],
        'data' => [
            'class' => null,
            'id' => null
        ]
    ];

    public static $config = self::config;

    public $union = [];
    public $data = [];

    protected $unit = [];
    protected $dent = [];

    // Build union attribute(s)…
    protected function _data($a, $unit = "") {
        if (!is_array($a)) {
            $a = trim((string) $a);
            return strlen($a) ? ' ' . $a : ""; // No hook(s) applied!
        }
        $output = "";
        $u = $this->union[1][1];
        $a = Hook::fire(__c2f__(static::class, '_') . '.data' . ($unit ? ':' . $unit : ""), [array_replace_recursive($this->data, $a), $unit]);
        foreach ($a as $k => $v) {
            if (!isset($v)) continue;
            if (__is_anemon__($v)) {
                $v = json_encode($v);
            }
            $output .= $u[3] . ($v !== true ? $k . $u[0] . $u[1] . static::x($v) . $u[2] : $k);
        }
        return $output;
    }

    // Base union constructor
    protected function _unite($unit, $content = "", $data = [], $dent = 0) {
        $dent = static::dent($dent);
        $u = $this->union[1][0];
        $s  = $dent . $u[0] . $unit . $this->_data($data, $unit);
        $s .= $content === false ? $u[1] : $u[1] . ($content ? $content : "") . $u[0] . $u[2] . $unit . $u[1];
        return Hook::fire(__c2f__(static::class, '_') . '.unit:' . $unit, [$s, [$unit, $content, $data]]);
    }

    // Inverse version of `Union::unite()`
    protected function _apart($input, $eval = true) {
        $u = $this->union[1][0];
        $d = $this->union[1][1];
        $x_u = isset($this->union[0][0]) ? $this->union[0][0] : [];
        $x_d = isset($this->union[0][1]) ? $this->union[0][1] : [];
        $u0 = isset($x_u[0]) ? $x_u[0] : x($u[0]); // `<`
        $u1 = isset($x_u[1]) ? $x_u[1] : x($u[1]); // `>`
        $u2 = isset($x_u[2]) ? $x_u[2] : x($u[2]); // `/`
        $d0 = isset($x_d[0]) ? $x_d[0] : x($d[0]); // `=`
        $d1 = isset($x_d[1]) ? $x_d[1] : x($d[1]); // `"`
        $d2 = isset($x_d[2]) ? $x_d[2] : x($d[2]); // `"`
        $d3 = isset($x_d[3]) ? $x_d[3] : x($d[3]); // ` `
        $input = trim($input);
        $output = [
            0 => null, // `Element.nodeName`
            1 => null, // `Element.innerHTML`
            2 => []    // `Element.attributes`
        ];
        $s = '/^' . $u0 . '(' . $u[3] . ')(' . $d3 . '.*?)?(?:' . $u2 . $u1 . '|' . $u1 . '(?:([\s\S]*?)(' . $u0 . $u2 . '\1' . $u1 . '))?)$/s';
        // Must starts with `<` and ends with `>`
        if ($u[0] && $u[1] && substr($input, 0, strlen($u[0])) === $u[0] && substr($input, -strlen($u[1])) === $u[1]) {
            // Does not match with pattern, abort!
            if (!preg_match($s, $input, $m)) {
                return false;
            }
            $output[0] = $m[1];
            $output[1] = isset($m[4]) ? $m[3] : false;
            if (!empty($m[2]) && preg_match_all('/' . $d3 . '+(' . $d[4] . ')(?:' . $d0 . $d1 . '((?:[^' . $d[1] . $d[2] . '\\\]|\\\.)*)' . $d2 . ')?/s', $m[2], $mm)) {
                foreach ($mm[1] as $k => $v) {
                    $s = To::html(v($mm[2][$k]));
                    $s = $eval ? e($s) : $s;
                    if ($s === "" && strpos($mm[0][$k], $d[0] . $d[1] . $d[2]) === false) {
                        $s = $v;
                    }
                    $output[2][$v] = $s;
                }
            }
            return $output;
        }
        return false;
    }

    // Union comment
    protected function ___($content = "", $dent = 0, $block = N) {
        $dent = static::dent($dent);
        $begin = $end = $block;
        if (strpos($block, N) !== false) {
            $end = $block . $dent;
        }
        $u = $this->union[1][2];
        return Hook::fire(__c2f__(static::class, '_') . '.unit:#', [$dent . $u[0] . $begin . $content . $end . $u[1], [null, $content, []]]);
    }

    // Base union unit open
    protected function _begin($unit = 'html', $data = [], $dent = 0) {
        $dent = static::dent($dent);
        $this->unit[] = $unit;
        $this->dent[] = $dent;
        $u = $this->union[1][0];
        return Hook::fire(__c2f__(static::class, '_') . '.begin:' . $unit, [$dent . $u[0] . $unit . $this->_data($data, $unit) . $u[1], [$unit, null, $data]]);
    }

    // Base union unit close
    protected function _end($unit = null, $dent = null) {
        if ($unit === true) {
            // Close all!
            $s = "";
            foreach ($this->unit as $u) {
                $s .= $this->_end() . ($dent ?: N);
            }
            return $s;
        }
        $unit = isset($unit) ? $unit : array_pop($this->unit);
        $dent = isset($dent) ? static::dent($dent) : array_pop($this->dent);
        $u = $this->union[1][0];
        return Hook::fire(__c2f__(static::class, '_') . '.end:' . $unit, [$unit ? $dent . $u[0] . $u[2] . $unit . $u[1] : "", [$unit, null, []]]);
    }

    public function __construct() {
        $this->union = static::$config['union'];
        $this->data = static::$config['data'];
    }

    // Indent…
    public static function dent($i) {
        return is_numeric($i) ? str_repeat(DENT, (int) $i) : $i;
    }

    // Encode all union’s special character(s)…
    public static function x($v) {
        return is_string($v) ? From::html($v) : $v;
    }

    public function __call($kin, $lot = []) {
        if (method_exists($this, '_' . $kin)) {
            return call_user_func_array([$this, '_' . $kin], $lot);
        }
        return parent::__call($kin, $lot);
    }

    public static function __callStatic($kin, $lot = []) {
        $that = new static;
        if (!self::_($kin) && method_exists($that, '_' . $kin)) {
            return call_user_func_array([$that, '_' . $kin], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}