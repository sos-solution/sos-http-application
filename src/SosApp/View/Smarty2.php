<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\View;

/**
 * Class View
 */
class Smarty2 extends Adapter
{
    /**
     * App
     *
     * @var mixed
     */
    private $twig;

    /**
     * __construct
     *
     * @param array $config
     */
    public function __construct($config) {

        $this->smarty = $smarty = new \Smarty();
        $smarty->template_dir = $config['view']['viewsDir'];
        $smarty->compile_dir  = $config['view']['cacheDir'];
        $smarty->cache_dir    = $config['view']['cacheDir'];
        $smarty->compile_check = true;
        $smarty->force_compile = true;
    }

    /**
     * Output Template
     *
     * @param  string   $route
     */
    public function template($route = FALSE) {
        $app = $this->app;

        if ( !$route ) {
            $route = $app->router->route;
        }

        $tplfile = $route . ".tpl";

        $app->language->load($route);
        $app->data['app'] = $app;
        $app->data['lang'] = $app->language;

        $this->smarty->assign($app->data);

        $this->smarty->display($tplfile);
    }

    /**
     * Output Template
     *
     * @param  string   $action
     * @param  string   $route
     */
    public function subtemplate($action, $route = FALSE) {
        $app = $this->app;

        if ( !$route ) {
            $route = $app->router->route;
        }

        $tplfile = "{$route}_{$action}" . ".tpl";

        $app->language->load($route);
        $app->data['app'] = $app;
        $app->data['lang'] = $app->language;
        
        $this->smarty->assign($app->data);

        $this->smarty->display($tplfile);
   }
}