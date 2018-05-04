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
 * Class Url
 */
class Url {
    /**
     * https
     *
     * @var integer https = 1, http = 0
     */
    public $https;

    /**
     * scheme
     *
     * @var string https or http
     */
    public $scheme;

    /**
     * host
     *
     * @var string 
     */
    public $host;

    /**
     * schemehost
     *
     * @var string 
     */
    public $schemehost;

    /**
     * dir
     *
     * @var string 
     */
    public $dir;

    /**
     * basename
     *
     * @var string 
     */
    public $basename;

    /**
     * filename
     *
     * @var string 
     */
    public $filename;

    /**
     * extension
     *
     * @var string 
     */
    public $extension;

    /**
     * path
     *
     * @var string 
     */
    public $path;

    /**
     * Setup 
     *
     * @param array $server
     * @param array $headers
     * @return void
     */
    public function __construct($server, $headers) {
        $https = $this->https = (
            (! empty($server['REQUEST_SCHEME']) && $server['REQUEST_SCHEME'] == 'https')
            || (! empty($server['HTTPS']) && $server['HTTPS'] == 'on') ||
               (! empty($server['SERVER_PORT']) && $server['SERVER_PORT'] == '443') )
            ? 1 : 0;

        $this->scheme = $https ? 'https' : 'http';

        $this->host   = isset($headers['X_FORWARDED_HOST']) ? $headers['X_FORWARDED_HOST'] : $headers['HOST'];

        $this->schemehost = $this->scheme . '://' . $this->host;
    }
}