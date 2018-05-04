<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Router;

/**
 * Class Exception
 */
class Exception extends \Exception {
	const METHOD_NOT_ALLOWED = 1;
	const HANDLER_NOT_FOUND  = 2;
	const ACTION_NOT_FOUND   = 3;
	const CLASS_NOT_FOUND    = 4;
	const FILE_NOT_FOUND     = 5;
	const INVALID_URL        = 6;
	const INVALID_CALL       = 7;

    /**
     * Route
     *
     * @var string
     */
	private $route  = '';

    /**
     * action
     *
     * @var string
     */
	private $action = '';

    /**
     * Method
     *
     * @var string
     */
	private $method = '';

    /**
     * ext
     *
     * @var string
     */
	private $ext = '';

    /**
     * Set
     *
     * @param string $route
     * @param string $action
     * @param string $method
     * @param string $ext
     *
     * @return array|void
     */
	function set($route, $action, $method, $ext) {
		$this->route  = $route;
		$this->action = $action;
		$this->method = $method;
		$this->ext    = $ext;
	}

    /**
     * Get
     *
     * @return array|void
     */
	function get() {
		return ['route'=>$this->route, 'action'=>$this->action, 'method'=>$this->method, 'ext'=>$this->ext];
	}
}
