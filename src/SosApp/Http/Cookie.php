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
 * Class Cookie
 */
class Cookie extends \SosApp\ArrayAccess {

    public $__c_path = '/';
    public $__c_domain = '';
    public $__c_secure = FALSE;
    public $__c_httponly = TRUE;

    /**
     * Set cookie
     *
     * @param string $name      Name
     * @param string $value     Value
     * @param string $expires   Zero or timestamp
     * @param bool $httponly    Only in http
     * @param secure $secure    Used in SSL
     */
    public function set($name, $value, $expires = 0, $httponly = NULL, $secure = NULL) {
        // 1262275200 = mktime(0, 0, 0, 1, 1, 2010) = 2010-01-01 00:00:00
        if ( $expires && $expires < 1262275200 ) {
            $expires = $expires + time();
        }        
        if ( $secure === NULL ) {
            $secure = $this->__c_secure;
        }
        if ( $httponly === NULL ) {
            $httponly = $this->__c_httponly;
        }
        $this[$name] = $value;
        return setcookie($name, $value, $expires, $this->__c_path, $this->__c_domain, $secure, $httponly);
    }

    public function get($name) {
        return $this[$name];
    }

    public function encode($key, $name, $value, $expires = 0, $httponly = NULL, $secure = NULL) {
        $this->set($name, \SosApp\JWT::encode($value, $key), $expires, $httponly, $secure);
    }

    public function decode($key, $name) {
        $value = $this[$name];
        if ( $value == '' ) {
            return FALSE;
        }
        return \SosApp\JWT::decode($value, $key);
    }

    public function encrypt($key, $name, $value, $expires = 0, $httponly = NULL, $secure = NULL) {
        $this->set($name, \SosApp\JWT::encrypt($value, $key), $expires, $httponly, $secure);
    }

    public function decrypt($key, $name) {
        $value = $this[$name];
        if ( $value == '' ) {
            return FALSE;
        }
        return \SosApp\JWT::decrypt($value, $key);
    }

    /**
     * Remove cookie
     *
     * @param string $name      Name
     */
    public function remove($name) {
        unset($this[$name]);
        return setcookie($name, '', 1, '/', '');
    }
}