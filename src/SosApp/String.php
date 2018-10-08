<?php
/**
 * SOS Framework (https://sos-framework.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) SOS Solution Limited. (https://www.sos-solution.com)
 * @license   https://sos-framework.com/license/new-bsd.html New BSD License
 */

namespace SosApp;

class String {
    private $func = NULL;
    public function __construct($func) {
        $this->func = $func;
    }
    
    static function create($func) {        
        return new self($func);
    }

    public function __toString() {
        $func = $this->func;
        return $func == NULL ? '' : $func();
    }
}
