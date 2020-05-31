<?php

class HTML extends SGML {

    public $strict = false;

    public function __construct($in = []) {
        parent::__construct($in);
        if (!empty($this->lot[2])) {
            foreach ($this->lot[2] as &$v) {
                if (is_string($v)) {
                    $v = htmlspecialchars_decode($v, $this->strict ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT | ENT_HTML5);
                }
            }
        }
    }

    public function __toString() {
        if (!empty($this->lot[2])) {
            $c = $this->c;
            foreach ($this->lot[2] as $k => &$v) {
                if (true === $v) {
                    continue;
                }
                if (!isset($v) || false === $v) {
                    unset($this->lot[2][$k]);
                    continue;
                }
                $v = htmlspecialchars(is_array($v) ? json_encode($v) : s($v), $this->strict ? ENT_COMPAT | ENT_XHTML : ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
            }
            unset($v);
        }
        return parent::__toString();
    }

}
