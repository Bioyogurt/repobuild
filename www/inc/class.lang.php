<?php

class lang {
    private $strings = array();
    private $lang;
    
    function __construct($lang='ru') {
        echo $this->lang = $lang;
    }
    function get($key) {
        if(!isset($this->strings[$this->lang])) {
            include 'inc'.DS.'langs'.$this->lang.'.php';
            foreach($strings as $name => $string) {
                $this->strings[$this->lang][$name] = $string;
            }
        }
        return $this->strings[$this->lang][$key];
    }
}