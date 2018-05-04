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
 * Class Header
 */
class Header extends \Sos\SimpleArrayObject {
    /**
     * Application
     *
     * @var Application
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
     * Get header
     *
     * @param name $name      Header type name
     * @param bool $exists    Return TRUE if header exists
     */
    public function get($name, &$exists = TRUE) {
        $name = strtoupper(str_replace('-', '_', $name));
        if (isset($this[$name])) {
            return $this[$name];
        }
        $exists = FALSE;
        return '';
    }

    /**
     * Set header
     *
     * @param string $name      Header type
     * @param string $value     Header content
     */
    public function set($name, $value) {
        return $this->app->swoole_response->header($name, $value);
    }

    /**
     * Set HTTP Status
     *
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     *     
     * @param integer $status   HTTP status
     */
    public function status($status) {
        $this->app->swoole_response->status((int)$status);
    }
}
