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
 * Class EventManager
 */

class EventManager {
    private $events;

    public function __construct() {
        $this->events = array();
    }

    public function attach($name, $callback, $priority = 0) {
        if ( !isset($this->events[$name]) ) {
            $this->events[$name] = new \SplPriorityQueue;
        }
        $this->events[$name]->insert($callback, $priority);
    }

    public function detachAll($name) {
        unset($this->events[$name]);
    }

    public function fire($name, $params = array()) {
        if ( !isset($this->events[$name]) ) {
            return;
        }

        $events = $this->events[$name];

        foreach ($events as $callback) {
            call_user_func_array($callback, $params);
        }
    }
}
