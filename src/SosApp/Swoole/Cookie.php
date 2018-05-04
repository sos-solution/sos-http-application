<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Swoole;

/**
 * Class Cookie
 */
class Cookie extends \Sos\SimpleArrayObject {
    /**
     * Application
     * 
     * @param  Application  $app
     */    
    public $app;

    /**
     * Set application container
     *
     * @param  \SosApp\Application|\SosApp\Soole\Application $app Application container
     */
    public function setApp($app) {
        $this->app = $app;
    }

    /**
     * Get application container
     *
     * @return  \SosApp\Application|\SosApp\Soole\Application Application container
     */
    public function getApp() {
        return $this->app;
    }

     /**
     * Set cookie
     *
     * @param string $name      Name
     * @param string $value     Value
     * @param string $expires   Zero or timestamp
     * @param bool $httponly    Only in http
     * @param secure $secure    Used in SSL
     */
   public function set($name, $value, $expires = 0, $httponly = TRUE, $secure = FALSE) {
        // 1262275200 = mktime(0, 0, 0, 1, 1, 2010) = 2010-01-01 00:00:00
        if ( $expires && $expires < 1262275200 ) {
            $expires = $expires + time();
        }
        $this[$name] = $value;
        return $this->app->swoole_response->cookie($name, $value, $expires, '/', '', $secure, $httponly);
    }

    /**
     * Remove cookie
     *
     * @param string $name      
     */
    public function remove($name) {
        unset($this[$name]);
        return $this->app->swoole_response->cookie($name, '', 1, '/', '');
    }
}