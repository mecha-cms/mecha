<?php

class To extends Genome {

    public static function __callStatic($kin, $lot = []) {
        if (!self::_($kin) && (!defined('DEBUG') || !DEBUG)) {
            return $lot[0];
        }
        return parent::__callStatic($kin, $lot);
    }

}