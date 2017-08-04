<?php

class State extends Genome {

    public static function __callStatic($kin, $lot = []) {
        $s = STATE . DS . $kin . '.php';
        if ($state = File::open($s)->import()) {
            $state_alt = array_merge(['shield' => ""], isset($lot[0]) ? (array) $lot[0] : []);
            $state = Anemon::extend($state_alt, $state);
            $s = SHIELD . DS . $state['shield'] . DS . 'state' . DS . $kin . '.php';
            if ($state_alt = File::open($s)->import()) {
                Anemon::extend($state, $state_alt);
            }
            return $state;
        }
        return parent::__callStatic($kin, $lot);
    }

    protected $lot = [];

    public function __construct($input = [], $lot = []) {
        $this->lot = array_replace($lot, $input);
        parent::__construct();
    }

    public function __call($key, $lot = []) {
        $fail = array_shift($lot);
        $fail_alt = array_shift($lot);
        $x = $this->__get($key);
        if (is_string($fail) && strpos($fail, '~') === 0) {
            return call_user_func(substr($fail, 1), $x !== null ? $x : $fail_alt);
        } else if ($fail instanceof \Closure) {
            return call_user_func($fail, $x !== null ? $x : $fail_alt);
        }
        return $x !== null ? $x : $fail;
    }

    public function __set($key, $value = null) {
        $this->lot[$key] = $value;
    }

    public function __get($key) {
        return array_key_exists($key, $this->lot) ? $this->lot[$key] : null;
    }

    public function __unset($key) {
        unset($this->lot[$key]);
    }

    public function __toString() {
        return json_encode($this->lot);
    }

}