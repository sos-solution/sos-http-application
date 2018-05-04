<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\ErrorHandler;

/**
 * Class File
 */
class File extends Adapter {

    /**
     * File path
     * 
     * @var $path
    */
    private $path = '';

    /**
     * Setup
     *
     * @param string $path
    */
    public function __construct($path) {
        $this->path = $path;
    }

    /**
     * Log function
     *
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     * @param array $errtrace
     * @param string $errmsg
    */
    public function log($errno, $errstr, $errfile, $errline, $errtrace, $errmsg) {
        error_log($errmsg, 3, $this->path);
    }
}