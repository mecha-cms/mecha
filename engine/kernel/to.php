<?php

final class To extends Genome {

    private function __construct() {}

    public static function __callStatic(string $kin, array $lot = []) {
        return parent::_($kin) ? parent::__callStatic($kin, $lot) : $lot[0];
    }

}