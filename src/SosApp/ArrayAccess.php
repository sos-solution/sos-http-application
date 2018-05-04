<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp;

class ArrayAccess implements \ArrayAccess {

    protected $_data = [];

    public function setData($data) {
        $this->_data = (array)$data;
    }

    public function isEmpty($offset)  {
        return $this[$offset] == '' ? 1 : 0;
    }

    public function isNotEmpty($offset)  {
        return $this[$offset] != '' ? 1 : 0;
    }
    
    public function offsetExists($offset) {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : '';
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    public function __get($name) {
        return $this[$name];
    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    public function getArray() {
        return $this->_data;
    }
}