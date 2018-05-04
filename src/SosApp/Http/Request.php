<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Http;

/**
 * Class Request
 */
class Request implements \ArrayAccess {

    /**
     * URI
     *
     * @var string
     */
    public    $uri;

    /**
     * timestamp
     *
     * @var integer
     */
    public    $timestamp;

    /**
     * timestampFloat
     *
     * @var float
     */
    public    $timestampFloat;

    /**
     * datetime
     *
     * @var \SosApp\DateTime
     */
    public    $datetime;

    /**
     * ip
     *
     * @var string IP in ipv4
     */
    public    $ip;

    /**
     * port
     *
     * @var integer
     */
    public    $port;

    /**
     * method
     *
     * @var string Request in lower case
     */
    public    $method;

    /**
     * defaultLanguage
     *
     * @var string 
     */
    public    $defaultLanguage;

    /**
     * language
     *
     * @var string 
     */
    public    $language;

    /**
     * acceptEncoding
     *
     * @var array
     */
    public    $acceptEncoding;

    /**
     * user
     *
     * @var string
     */
    public    $user              = '';

    /**
     * password
     *
     * @var string
     */
    public    $password          = '';

    /**
     * data
     *
     * @var array
     */
    protected $_data             = [];

    /**
     * Application
     *
     * @var Application
     */
    public    $app;

    /**
     * Get request method
     *
     * @return string 
     */
    public function method() {
        return $this->method;
    }

    /**
     * Get request html body in raw
     *
     * @return string 
     */
    public function raw() {
        return file_get_contents('php://input');
    }

    /**
     * Parse accept encoding header content
     *
     * @param string $encoding
     * @return array
     */
    public function praseAcceptEncoding($encoding) {
        if ( $encoding == '' ) {
            return [];
        }

        $list   = explode(',', $encoding);
        $result = [];
        foreach ( $list as $encode ) {
            $encode = trim($encode);
            $result[] = $encode;
        }
        return $result;
    }    

    /**
     * Get language locale from language input
     *
     * @param string $accept
     * @param array $languages
     * @param string $defaultLanguage
     * @param booll find Reserved     
     * @return array
     */
    public function getLanguageLocale($accept, $languages, $defaultLanguage, $find = FALSE) {
        if ( $find || !isset($_COOKIE['_L']) ) {
            $defaultLocale = '';

            if ( $accept != '' ) {
                $prefLocales = array_reduce(
                    explode(',', $accept), 
                    function ($res, $el) { 
                        list($l, $q) = array_merge(explode(';q=', $el), [1]);
                        $res[$l] = (float) $q; 
                        return $res; 
                    }, 
                    []);
                arsort($prefLocales);
                
                $langList = $languages;

                $found = false;
                
                foreach ( $prefLocales as $locale => $_val ) {
                    foreach ( $langList as $langLocale => $item ) {
                        if ( strpos(",{$item[2]},", ",{$locale},") !== FALSE ) {
                            $found = true;
                            $defaultLocale = $langLocale;
                            break;
                        }
                    }
                    if ( $found ) {
                        break;
                    }
                }
            }

            if ( $defaultLocale == '' ) 
                $defaultLocale = $defaultLanguage;

            // 31536000 = 1 YEAR
            setcookie('_L', $defaultLocale, time() + 31536000, '/', '', FALSE, TRUE);
            $_COOKIE['_L'] = $defaultLocale;
            return $defaultLocale;
        } else {
            if ( isset($languages[$_COOKIE['_L']]) ) {
                return $_COOKIE['_L'];
            }
        }
        return $this->getLanguageLocale($accept, $languages, $defaultLanguage, TRUE);
    }

    /**
     * Set request data
     *
     * @param array $data
     * @return void
     */
    public function setData($data) {
        $this->_data = (array)$data;
    }

    /**
     * Is request array empty ? 
     *
     * @param string $offset
     * @return bool
     */
    public function isEmpty($offset)  {
        return $this[$offset] == '' ? 1 : 0;
    }

    /**
     * Is request array not empty ? 
     *
     * @param string $offset
     * @return bool
     */
    public function isNotEmpty($offset)  {
        return $this[$offset] != '' ? 1 : 0;
    }
    
    /**
     * Get content from request by key
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetExists($offset) {
        $name_a = explode('.', $offset);
        $current = &$this->_data;
        $found = TRUE;
        foreach ( $name_a as $name ) {
            if ( isset($current[$name]) ) {
                $current = &$current[$name];
            } else {
                $found = FALSE;
                break;
            }
        }
        return $found;
    }

    /**
     * Remove content from request by key
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) {
        $name_a = explode('.', $offset);
        $name_c = count($name_a);
        $current = &$this->_data;
        $found = TRUE;
        $count = 0;

        foreach ( $name_a as $name ) {
            ++$count;

            if ( isset($current[$name]) == FALSE )
                break;
            
            if ($count==$name_c) {
                unset($current[$name]);
                break;
            }

            $current = &$current[$name];
        }
    }

    /**
     * Get content from request by key
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        switch ( $offset ) {
            case '__REQ_DATETIME__':
                return $this->app->datetime->format('Y-m-d H:i:s');
            case '__REQ_DATE__':
                return $time->app->datetime->format('Y-m-d');
            case '__REQ_TIME__':
                return $time->app->datetime->format('H:i:s');
            case '__DATETIME__':
                return $this->app->datetime->date('Y-m-d H:i:s');
            case '__DATE__':
                return $time->app->datetime->date('Y-m-d');
            case '__TIME__':
                return $time->app->datetime->date('H:i:s');
            case '__IP__':
                return $this->ip;
            case '__AUTH_UESR__':
                return $this->user;
            case '__AUTH_PASS__':
                return $this->password;
        }

        $name_a = explode('.', $offset);
        $current = &$this->_data;
        $found = TRUE;
        foreach ( $name_a as $name ) {
            if ( isset($current[$name]) ) {
                $current = &$current[$name];
            } else {
                $found = FALSE;
                break;
            }
        }
        return $found ? $current : '';
    }

    /**
     * Set content to request by key
     *
     * @param string $offset
     * @param string $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $name_a = explode('.', $offset);
            $name_c = count($name_a);
            $current = &$this->_data;
            $found = TRUE;
            $count = 0;

            foreach ( $name_a as $name ) {
                ++$count;

                if ( isset($current[$name]) == FALSE && $count < $name_c)
                    $current[$name] = array(); 

                $current = &$current[$name];
                if ($count==$name_c) {
                    $current = trim($value);
                }
            }
        }
    }

    /**
     * Get content from request by key
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this[$name];
    }

    /**
     * Set content to request by key
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    /**
     * Set request content
     *
     * @return array
     */
    public function getArray() {
        return $this->_data;
    }    

    /**
     * Set Application
     *
     * @param Application $app  Application
     */
    public function setApp($app) {
        $this->app = $app;
    }

    /**
     * Get Application
     *
     * @return Application
     */
    public function getApp() {
        return $this->app;
    }

    public function redirect($uri, $status_code = 302) {
        $this->app->header->status($status_code);
        $this->app->header->set('Location', $uri);
        exit;
    } 
}