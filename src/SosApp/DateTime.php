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
 * Class DateTime
 */
class DateTime extends \DateTime {

    public function date($format, $timestamp = 0) {        
        $this->setTimestamp($timestamp ? $timestamp : time());
        return $this->format($format);
    }

    public function __toString() {
        $this->setTimestamp(time());
        return $this->format('Y-m-d H:i:s');
    }
}