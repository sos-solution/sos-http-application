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
 * Class Controller
 */
class Controller {
    protected $_route   = '';

    protected $_action  = '';

    protected $data   = [];

    protected $app;

    public function __construct() {
    }

    public function initialize() {
    }

    public function setApp($app) {
        $this->app = $app;
    }

    public function getApp() {
        return $this->app;
    }

    public function setRoute($route) {
        $this->_route = $route;
    }

    public function setAction($action) {
        $this->_action = $action;
    }

    public function controller($route) {
        $class      = 'controller_' . str_replace('/', '_', $route);
        $controller = new $class;
        $controller->setApp($this->app);
        $controller->setRoute($route);
        $controller->setAction('');
        return $controller;
    }

    public function __get($name) {
        return $this->app->$name;
    }

    public function template($route = NULL) {
        $this->app->data = $this->data;
        return $this->app->view->template($route ? $route : $this->_route);
    }

    public function subtemplate($action = NULL, $route = NULL) {
        $this->app->data = $this->data;
        return $this->app->view->subtemplate($action ? $action : $this->_action, $route ? $route : $this->_route);
    }

    public function subtemplate2($sub_action = NULL, $route = NULL) {
        $this->app->data = $this->data;
        return $this->app->view->subtemplate($this->_action . '_' . $sub_action, $route ? $route : $this->_route);
    }
}