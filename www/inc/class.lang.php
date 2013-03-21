<?php

class lang {
    private $_strings = array();
    private $_lang;
    
    function __construct($lang='en') {
        $this->_lang = $lang;
    }
    function __get($key) {
        if(!isset($this->_strings[$this->_lang])) {
            $file = 'inc'.DS.'langs'.DS.$this->_lang.'.php';
            if(is_file($file))
                include $file;
            else
                die('Critical Error');
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
