<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Swoole;

/**
 * Class Application
 */
class Application extends \Sos\Di {
    /**
     * swoole_request
     * 
     * @var object
     */
    public $swoole_request;

    /**
     * swoole_response
     * 
     * @var object
     */
    public $swoole_response;

    /**
     * Browser
     * 
     * @var \SosApp\Http\Browser
     */
    public $browser;

    /**
     * Router
     *
     * @var \SosApp\Router
     */
    public $router;

    /**
     * Url
     *
     * @var \SosApp\Http\Url
     */
    public $url;

    /**
     * Header
     *
     * @var \SosApp\Http\Header
     */
    public $header;

    /**
     * Url
     *
     * @var \SosApp\Http\Params
     */
    public $params;

    /**
     * Request
     *
     * @var \SosApp\Http\Request
     */
    public $request;

    /**
     * Cookie
     *
     * @var \SosApp\Http\Cookie
     */
    public $cookie;

    /**
     * Session
     *
     * @var \SosApp\Session\Adapter
     */
    public $session;

    /**
     * Translate Object
     *
     * @var \SosApp\Translate\Adapter
     */
    public $language;

    /**
     * DateTime
     *
     * @var \SosApp\DateTime
     */
    public $datetime;

    /**
     * Timezone string
     *
     * @var string
     */
    public $timezone   = 'UTC';

    /**
     * View
     *
     * @var \SosApp\View\Adapter
     */
    public $view       = NULL;

    /**
     * Database
     *
     * @var \Doctrine
     */
    public $db         = NULL;

    /**
     * SQL Query
     *
     * @var \object
     */
    public $sql        = NULL;

    /**
     * Event
     *
     * @var \SosApp\Event
     */
    public $event      = NULL;

    /**
     * Setup the request
     *
     * @param object $sw_request   Request object
     * @param object $sw_response  Response object
     * @param string $composer_dir  Composer Directory
     * @param callable $handler    Callable function($di, $config)
     * @param array $options        Options
     */    
    public function __construct($sw_request, $sw_response, $composer_dir, $handler, $options = []) {
        ob_start();

        $this->swoole_request  = $sw_request;
        $this->swoole_response = $sw_response;

        $server  = array_change_key_case($sw_request->server, CASE_UPPER);
        $headers = array_change_key_case($sw_request->header, CASE_UPPER);
        $uri     = explode('?', $server['REQUEST_URI'], 2)[0];

        if ( isset($options['config']) ) {
            $config = $options['config'];
        } else {
            $config = parse_ini_file(APP_DIR . 'app/config/config.ini', TRUE, INI_SCANNER_NORMAL);
        }

        if ( isset($options['language']) ) {
            $lang_config = $options['language'];
        } else {
            $lang_config = include APP_DIR . 'app/languages.php';
        }

        $appcfg = $config['application'];

        date_default_timezone_set(isset($appcfg['timezone']) ? $appcfg['timezone'] : $this->timezone);

        # Init Header
        $header = $this->header = new \Sos\Swoole\Header;
        $header->setApp($this);

        foreach ( $headers as $key => $value ) {
            $header[str_replace('-', '_', $key)] = $value;
        }

        # Init URL / Route
        $router = $this->router = new \Sos\Router();
        $router->setApp($this);
        $urlinfo = $router->parseURL($uri, TRUE);

        # Init Request Object
        $request = $this->request = new \Sos\Http\Request;

        $request->timestamp = $server['REQUEST_TIME'];

        $request->timestampFloat = $server['REQUEST_TIME_FLOAT'];

        $request->uri      = $server['REQUEST_URI'];

        $request->datetime = date('Y-m-d H:i:s', $server['REQUEST_TIME']);

        $request->ip       = $server['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $server['REMOTE_ADDR'];

        $request->port     = $server['REMOTE_PORT'];

        $request->method   = strtolower($server['REQUEST_METHOD']);

        $request->setData(array_merge(
            isset($sw_request->get) ? $sw_request->get : [],
            isset($sw_request->post) ? $sw_request->post : []
        ));        

        $request->defaultLanguage = $lang_config['default'];

        $request->language = $request->getLanguageLocale(isset($server['HTTP_ACCEPT_LANGUAGE'])
            ? $server['HTTP_ACCEPT_LANGUAGE'] : '', $lang_config['list'], $lang_config['default']);

        $request->acceptEncoding = $request->praseAcceptEncoding(isset($server['HTTP_ACCEPT_ENCODING'])
            ? $server['HTTP_ACCEPT_ENCODING'] : '');

        # Init Request Params
        $params = $this->params = new \Sos\Http\Params;

        # Init URL
        $url       = new \Sos\Http\Url($server, $headers);
        $this->url = $url;

        if ( $urlinfo ) {
            $url->dir        = $urlinfo['dir'];
            $url->basename   = $urlinfo['basename'];
            $url->filename   = $urlinfo['filename'];
            $url->path       = $urlinfo['path'];
            $url->extension  = $urlinfo['format'];

            $router->success = 1;
            $router->class   = $urlinfo['class'];
            $router->action  = $urlinfo['action'];
            $router->format  = $urlinfo['format'];
            $router->method  = $request->method;
            $router->route   = $urlinfo['route'];

            $params->setData($urlinfo['params']);
        }

        # Init Cookie
        $cookie = $this->cookie = new \Sos\Swoole\Cookie;
        $cookie->setApp($this);
        $cookie->setData(isset($sw_request->cookie) ? $sw_request->cookie : []);

        # Init Browser
        $browser = $this->browser = new \Sos\Http\Browser;
        $browser->setApp($this);
        $browser->parseBrowser();

        # Init View
        $view = $this->view = new \Sos\Mvc\View($config);
        
        $view->setApp($this);

        # Init Event
        $this->event = new \SosApp\EventManager;

        # Init Language
        $language = $this->language = new \SosApp\Translate\Adapter\PHP(
            $request->defaultLanguage, 
            $request->language, 
            $lang_config['list']);

        $language->setBaseDir($appcfg['langsDir']);

        $handler = \Closure::bind($handler, $this, get_called_class());

        $handler($this, $config, $sw_request, $sw_response);

        $content = ob_get_contents();
        ob_end_clean();

        $sw_response->end($content);
    }

     /**
     * Dump all variable
     *
     * @param bool $return return variable if true
     *
     * @return array|void
     */
   function dump($return = FALSE) {
        return print_r($this, $return);
    }
}