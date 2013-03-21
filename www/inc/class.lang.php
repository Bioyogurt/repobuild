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
            foreach($lang as $name => $string) {
                $this->_strings[$this->_lang][$name] = $string;
            }
        }
        if(isset($this->_strings[$this->_lang][$key]))
            return $this->_strings[$this->_lang][$key];
        else
            die('Error with lang file');
    }
    function set($lang) {
        $this->_lang = $lang;
    }
}