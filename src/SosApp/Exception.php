<?php 
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp;

/**
 * Class Exception
 */
class Exception extends \Exception {
    public function __construct($message = "", $code = 0, $previous = 0) {
        
        parent::__construct($message, $code, NULL);

        if ( $previous ) {
            $errtrace = debug_backtrace();
            array_shift($errtrace);

            $max = count($errtrace);

            if ( $previous > $max ) {
                $previous = $max;
            }

            for ( $i = 0; $i < $previous; $i++ ) {
                $error = array_shift($errtrace);
            }
            $this->file = $error['file'];
            $this->line = $error['line'];
        }        
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function setCode($code) {
        $this->code = $code;
    }

    public function setMessage($message) {
        $this->message = $message;
    }
}