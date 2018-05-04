<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\View;

/**
 * Class View
 */
abstract class Adapter
{
    /**
     * App
     *
     * @var mixed
     */
    protected $app;

    /**
     * __construct
     *
     * @param array $config
     */
    public function __construct($config) {
    
    }

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
     * Output Template
     *
     * @param  string   $route
     */
    abstract public function template($route = FALSE);

    /**
     * Output Template
     *
     * @param  string   $action
     * @param  string   $route
     */
    abstract public function subtemplate($action, $route = FALSE);
}