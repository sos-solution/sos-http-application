<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Translate;

/**
 * Class Adapter
 */
abstract class Adapter implements \ArrayAccess {
    public $locale         = '';

    public $defaultLocale  = '';

    public $overwirteMode  = TRUE;

    protected $baseDir        = '';

    private   $langList       = [];

    private   $_data          = [];

    /**
     * __construct
     *
     * @param string $defaultLocale
     * @param string $locale
     * @param string $langList
     * @return secure $secure 
     */
    public function __construct($defaultLocale, $locale, $langList) {
        $this->defaultLocale = $defaultLocale;
        $this->locale        = $locale == '' ? $defaultLocale : $locale;

        foreach ($langList as $code => $item ) {
            $this->langList[$code] = array('code'=>$code, 'flag'=>$item[0], 'name'=>$item[1], 'accept' => $item[2]);
        }
    }

    /**
     * setLocale
     *
     * @param string $overwirteMode
     */
    public function setOverwriteMode($overwirteMode = TRUE) {
        $this->overwirteMode = $overwirteMode;
    }

    /**
     * offsetSet
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        if ( !isset($this->_data[$this->locale]) ) {
            $this->_data[$this->locale] = [];
        }

        $_data = &$this->_data[$this->locale];

        if (is_null($offset)) {
            $_data[] = $value;
        } else {
            $_data[$offset] = $value;
        }
    }

    /**
     * offsetExists
     *
     * @param string $offset
     * @return bool   
     */
    public function offsetExists($offset) {
        if ( !isset($this->_data[$this->locale]) ) {
            return FALSE;
        }

        $_data = &$this->_data[$this->locale];
        return isset($_data[$offset]);
    }

    /**
     * offsetUnset
     *
     * @param string $offset
     */
    public function offsetUnset($offset) {
        if ( !isset($this->_data[$this->locale]) ) {
            return;
        }

        unset($this->_data[$this->locale][$offset]);
    }

    /**
     * offsetGet
     *
     * @param string $offset
     */
    public function offsetGet($offset) {
        // Current locale
        if ( !isset($this->_data[$this->locale]) ) {
            return $offset;
        }

        $_data = &$this->_data[$this->locale];

        if ( isset($_data[$offset]) ) {
            return $_data[$offset];
        }

        // Current locale
        if ( !isset($this->_data[$this->defaultLocale]) ) {
            return $offset;
        }

        $_data = &$this->_data[$this->defaultLocale];

        if ( isset($_data[$offset]) ) {
            return $_data[$offset];
        }
        return $offset;
    }

    /**
     * L
     *
     * @param string $offset
     * @param mixed $args
     * @return secure $secure 
     */
    public function L($offset, $args = NULL) {
        return ( $args ) ? vsprintf($this[$offset], $args) : $this[$offset];
    }

    /**
     * _
     *
     * @param string $offset
     * @param mixed $args
     * @return secure $secure 
     */
    public function _($offset, $args = NULL) {
        return ( $args ) ? vsprintf($this[$offset], $args) : $this[$offset];
    }

    /**
     * getLocaleObject
     *
     * @param string $locale
     * @return mixed
     */
    protected function &getLocaleObject($locale) {
        if ( !isset($this->_data[$locale]) ) {
            $this->_data[$locale] = [];
        }
        return $this->_data[$locale];
    }

    public function __get($name) {
        return isset($this->_data[$name]) ? $this->_data[$name] : $name;
    }

    /**
     * setBaseDir
     *
     * @param string $baseDir 
     */
    public function setBaseDir($baseDir) {
        $this->baseDir = rtrim($baseDir, '/\\');
    }

    /**
     * setLocale
     *
     * @param string $locale 
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    public function getLocale($locale) {
        return $this->locale;
    }

    public function setDefaultLocale($locale) {
        return $this->defaultLocale = $locale;
    }

    public function getDefaultLocale() {
        return $this->defaultLocale;
    }
    
    /**
     * Load language
     *
     * @param string $route
     */
    public function load($route) {
        return $this->parseFile($route, $this->locale);
    }

    /**
     * Get language
     *
     * @return array
     */
    public function getLanguages() {
        return $this->langList;
    }

    public function getLanguage() {
        return [$this->langList[$this->locale]];
    }

    /**
     * parseFile
     *
     * @param string $filename
     * @param string $locale
     */
    abstract protected function parseFile($filename, $locale);
}