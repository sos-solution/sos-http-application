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
 * Class Output
 */
class Output extends Adapter {
    /**
     * Handler
     * 
     * @var $handler
    */    
    private $handler;

    /**
     * Set up handler for output
     *
     * @param callable $handler function($errno, $errstr, $errfile, $errline, $errtrace, $errmsg)
    */
    public function __construct($handler = NULL) {
        $this->handler = $handler;
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
        if ( $this->handler ) {
            $handler = $this->handler;
            $handler($errno, $errstr, $errfile, $errline, $errtrace, $errmsg);
        }
    }
}