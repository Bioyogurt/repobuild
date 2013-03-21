<?php

class lang {
    private $_strings = array();
    private $_lang;
    
    function __construct($lang='ru') {
        $this->_lang = $lang;
    }
    function __get($key) {
        if(!isset($this->_strings[$this->_lang])) {
            include 'inc'.DS.'langs'.DS.$this->_lang.'.php';
            foreach($strings as $name => $string) {
                $this->_strings[$this->_lang][$name] = $string;
            }
        }
        return $this->_strings[$this->_lang][$key];
    }
}