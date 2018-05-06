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
class Twig extends Adapter
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
    public function __construct($config, $app) {
        $this->setApp($app);
        $loader = new \Twig_Loader_Filesystem($config['view']['viewsDir']);

        $options = [
            'auto_reload' => true,
            'debug' => false,
            'autoescape' => false
        ];

        if ( isset($config['twig']) ) {
            $twig = $config['twig'];
            foreach ( $twig as $key => $val ) {
                $options[$key] = $val;
            }
        }

        $twig = new \Twig_Environment($loader, $options);

        if ( isset($config['view']['pluginsDir']) ) {
            $pluginsDir = $config['view']['pluginsDir'];
            $files = glob($pluginsDir . '/*.php');
            $pluginsDirLen = strlen($pluginsDir);

            if ( $files ) {
                foreach ( $files as $file ) {
                    // $filename format [type].[name].php, e.g. filter.test_name.php
                    $filename = substr($file, $pluginsDirLen + 1);
                    $part = explode('.', $filename);
                    $object = include $file;

                    if ( is_object($object) == FALSE && is_array($object) == FALSE ) {
                        trigger_error("Extending Twig return NULL in file:$file", E_USER_WARNING);
                        continue;
                    }

                    switch ( $part[0] ) {
                        case 'filter':
                            $twig->addFilter($object);
                            break;
                        case 'function':
                            $twig->addFunction($object);
                            break;
                        case 'extension':
                            $twig->addExtension($object);
                            break;
                        case 'global':
                            $twig->addGlobal($part[1], $object);
                            break;
                        case 'test':
                            $twig->addTest($object);
                            break;
                        case 'tokenparser':
                            $twig->addTokenParser($object);
                            break;
                        case 'runtimeloader':
                            $twig->addRuntimeLoader($object);
                            break;
                    }
                }
            }
        }
        $this->twig = $twig;
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

        $tplfile = $route . ".twig";

        $app->language->load($route);
        $app->data['app'] = $app;
        $app->data['lang'] = $app->language;

        echo $this->twig->render($tplfile, $app->data);
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

        $tplfile = "{$route}_{$action}" . ".twig";

        $app->language->load($route);
        $app->data['app'] = $app;
        $app->data['lang'] = $app->language;
        
        echo $this->twig->render($tplfile, $app->data);
    }
}