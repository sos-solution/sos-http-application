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
 * Class Di
 */
class Di implements \ArrayAccess
{
    protected $_container = array();

    public function __construct(array $container = array()) {
        $this->_container = $container;
    }

    public function offsetSet($id, $value) {
        $this->_container[$id] = $value;
    }

    public function set($id, $value) {
        $this->_container[$id] = $value;
    }

    public function __set($id, $value) {
        $this->_container[$id] = $value;
    }

    public function offsetGet($id) {
        if (!isset($this->_container[$id])) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }
        $object = $this->_container[$id];
        $isFactory = is_object($object) && method_exists($object, '__invoke');
        return $isFactory ? $object($this) : $object;
    }

    public function __get($id) {
        if (!isset($this->_container[$id])) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }
        $object = $this->_container[$id];
        $isFactory = is_object($object) && method_exists($object, '__invoke');
        return $isFactory ? $object($this) : $object;
    }

    public function offsetExists($id) {
        return array_key_exists($id, $this->_container);
    }

    public function offsetUnset($id) {
        unset($this->_container[$id]);
    }

    public function setShared($id, $callable) {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }
        $this->_container[$id] = function ($c) use ($callable) {
            static $object;
            if (null === $object) {
                $object = $callable($c);
            }
            return $object;
        };
    }

    public function setProtected($id, $callable) {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException('Callable is not a Closure or invokable object.');
        }
        $this->_container[$id] = function ($c) use ($callable) {
            return $callable;
        };
    }

    public function __call($id, $args) {
        if (!isset($this->_container[$id]) || !is_object($this->_container[$id]) || !method_exists($this->_container[$id], '__invoke')) {
            throw new \InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }
        return call_user_func_array($this[$id], $args);
    }

    public function getRaw($id) {
        if (!isset($this->_container[$id])) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }
        return $this->_container[$id];
    }
}