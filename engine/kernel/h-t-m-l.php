<?php

class HTML extends Union {

    public static $config = self::config;

    public function __construct() {
        parent::__construct();
        $this->data = [
            'class' => null,
            'id' => null,
            'src' => null,
            'alt' => null,
            'width' => null,
            'height' => null,
            'property' => null,
            'name' => null, // [1]
            'content' => null,
            'href' => null,
            'rel' => null,
            'target' => null,
            'type' => null, // [2]
            'action' => null,
            'method' => null,
            'enctype' => null,
            'value' => null, // [3]
            'placeholder' => null, // [4]
            'label' => null,
            'selected' => null,
            'checked' => null,
            'disabled' => null,
            'readonly' => null,
            'style' => null
        ];
    }

    // Build HTML attribute(s)…
    protected function _data($a, $unit = "") {
        if (is_array($a)) {
            foreach ($a as $k => $v) {
                if (!is_array($v)) continue;
                // HTML5 `data-*` attribute
                if ($k === 'data[]') {
                    foreach ($v as $kk => $vv) {
                        if (!isset($vv)) continue;
                        $a['data-' . $kk] = __is_anemon__($vv) ? json_encode($vv) : $vv;
                    }
                    unset($a[$k]);
                // Class value as array
                } else if ($k === 'class[]') {
                    if (isset($a['class'])) {
                        $v = array_merge(explode(' ', $a['class']), $v);
                    }
                    $a['class'] = implode(' ', array_filter(array_unique($v)));
                    unset($a[$k]);
                // Inline CSS via `style[]` attribute
                } else if ($k === 'style[]') {
                    $css = "";
                    foreach ($v as $kk => $vv) {
                        if (!isset($vv)) continue;
                        $css .= $kk . ':' . $vv . ';';
                    }
                    $a['style'] = $css;
                    unset($a[$k]);
                }
            }
        }
        return parent::_data($a, $unit);
    }

    protected function _apart($input, $eval = true) {
        $output = parent::_apart($input, $eval);
        if (!empty($output[2])) {
            foreach ($output[2] as $k => $v) {
                if (strpos($k, 'data-') === 0) {
                    $output[2]['data[]'][substr($k, 5)] = $v;
                } else if ($k === 'class') {
                    $output[2]['class[]'] = array_unique(explode(' ', $v));
                } else if ($k === 'style') {
                    if (preg_match_all('#(?:^|;)\s*([a-z\d-]+)\s*:\s*(.*?)\s*(?:;|$)#', $v, $m)) {
                        foreach ($m[1] as $k => $v) {
                            $output[2]['style[]'][$v] = $m[2][$k];
                        }
                    }
                }
            }
        }
        return $output;
    }

    public function __call($kin, $lot = []) {
        if (!self::_($kin) && !method_exists($this, '_' . $kin)) {
            array_unshift($lot, $kin);
            return call_user_func_array([$this, 'unite'], $lot);
        }
        return parent::__call($kin, $lot);
    }

    public static function __callStatic($kin, $lot = []) {
        $that = new static;
        if (!self::_($kin) && !method_exists($that, '_' . $kin)) {
            array_unshift($lot, $kin);
            return call_user_func_array([$that, 'unite'], $lot);
        }
        return parent::__callStatic($kin, $lot);
    }

}