<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp\Http;

/**
 * Class Application
 */
class Application extends \SosApp\Di {
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
     * Event
     *
     * @var \SosApp\Event
     */
    public $event      = NULL;

    /**
     * Data
     *
     * @var array
     */
    public $data      = [];

    /**
     * Keys
     *
     * @var array
     */
    public $keys      = [];

    /**
     * Setup the request
     *
     * @param string $composer_dir  Composer Directory
     * @param callable $handler    Callable function($di, $config)
     * @param array $options        Options
     */
    public function __construct($composer_dir, $handler, $options = []) {
        $uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];

        # Init Config
        if ( isset($options['config']) ) {
            $config = $options['config'];
        } else {
            $config = parse_ini_file(APP_DIR . 'app/config/config.ini', TRUE, INI_SCANNER_NORMAL);
        }

        # Init Language
        if ( isset($options['language']) ) {
            $lang_config = $options['language'];
        } else {
            $lang_config = include APP_DIR . 'app/languages.php';
        }

        $appcfg = $config['application'];

        if ( isset($appcfg['timezone']) ) {
            $this->timezone = $appcfg['timezone'];
        }

        # Init Header
        $header = $this->header = new \SosApp\Http\Header;
        foreach ( $_SERVER as $key => $value ) {
            if ( strpos($key, 'HTTP_') === 0) {
                $header[substr($key, 5)] = $value;
            }
        }

        # Init URL / Route
        $router = $this->router = new \SosApp\Router();

        $router->setApp($this);

        $urlinfo = $router->parseURL($uri, TRUE);

        # Init Request Object
        $request = $this->request = new \SosApp\Http\Request;

        $request->setApp($this);

        $request->timestamp = $_SERVER['REQUEST_TIME'];

        $request->timestampFloat = $_SERVER['REQUEST_TIME_FLOAT'];

        $request->uri      = $_SERVER['REQUEST_URI'];

        $request->ip       = $_SERVER['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];

        $request->port     = $_SERVER['REMOTE_PORT'];

        $request->method   = strtolower($_SERVER['REQUEST_METHOD']);

        $request->defaultLanguage = $lang_config['default'];

        $request->language = $request->getLanguageLocale(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '', $lang_config['list'], $lang_config['default']);

        $request->acceptEncoding = $request->praseAcceptEncoding(isset($_SERVER['HTTP_ACCEPT_ENCODING'])
            ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '');

        $request->setData($_REQUEST);

        # Init Request Params
        $params = $this->params = new \SosApp\Http\Params;

        # Init URL
        $url       = new \SosApp\Http\Url($_SERVER, $header);
        $this->url = $url;

        if ( $urlinfo ) {
            $url->dir        = $urlinfo['dir'];
            $url->basename   = $urlinfo['basename'];
            $url->filename   = $urlinfo['filename'];
            $url->path       = $urlinfo['path'];
            $url->extension  = $urlinfo['ext'];

            $router->success = 1;
            $router->class   = $urlinfo['class'];
            $router->action  = $urlinfo['action'];
            $router->ext     = $urlinfo['ext'];
            $router->method  = $request->method;
            $router->route   = $urlinfo['route'];

            $params->setData($urlinfo['params']);
        }

        # Init Cookie
        $cookie = $this->cookie = new \SosApp\Http\Cookie;
        $cookie->setData($_COOKIE);

        # Init Browser
        $browser = $this->browser = new \SosApp\Http\Browser;

        $browser->setApp($this);
        $browser->parseBrowser();

        # Init Datetime
        $this->datetime = new \SosApp\DateTime;

        $this->setTimezone($this->timezone);

        # Init View
        $view = $this->view = new $config['di']['view']($config, $this);
        
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

        $handler($this, $config);
    }

    /**
     * Preload Config File
     *
     * @param callable $handler  Handler
     */
    static public function initConfig($handler) {
        if ( is_readable(APP_DIR . 'app/config/config.ini') ) {
            $config = parse_ini_file(APP_DIR . 'app/config/config.ini', TRUE, INI_SCANNER_NORMAL);    
        } else {
            $config = parse_ini_file(COMPOSER_DIR . 'core/config/config.ini', TRUE, INI_SCANNER_NORMAL);
        }

        $phpini = $config['php.ini'];

        foreach ( $phpini as $key => $value ) {
            ini_set($key, $value);
        }

        $handler($config);

        return $config;
    }


    /**
     * Set global timezone
     *
     * @see http://php.net/manual/en/timezones.php
     *
     * @param string $timezone  Timezone
     */
    public function setTimezone($timezone) {

        $this->timezone = $timezone;
        $datetime = $this->datetime;
        $datetime->setTimezone(new \DateTimeZone($timezone));
        $this->request->datetime = $datetime->date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
    }

    /**
     * Get global timezone
     *
     * @see http://php.net/manual/en/timezones.php
     *
     * @return string $timezone  Timezone
     */
    public function getTimezone() {
        return $this->timezone;
    }

    public function setLanguage($locale) {
        $languages = $this->language->getLanguages();
        if ( isset($languages[$locale]) ) {
            setcookie('_L', $locale, time() + 31536000, '/', '', FALSE, TRUE);            
        }
    }

    /**
     * Dump all variable
     *
     * @param bool $return return variable if true
     *
     * @return array|void
     */
    public function dump($return = FALSE) {
        return print_r($this, $return);
    }
}