<?php

class lang {
    private $strings = array();
    private $lang;
    
    function __construct($lang='ru') {
        $this->lang = $lang;
    }
    function __get($key) {
        if(!isset($this->strings[$this->lang])) {
            include 'inc'.DS.'langs'.DS.$this->lang.'.php';
            foreach($strings as $name => $string) {
                $this->strings[$this->lang][$name] = $string;
            }
        }
        return $this->strings[$this->lang][$key];
    }
}