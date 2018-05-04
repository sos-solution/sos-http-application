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
 * Class Header
 */
class Header extends \SosApp\ArrayAccess {

    /**
     * Get header
     *
     * @param name $name      Header type name
     * @param bool $exists    Return TRUE if header exists
     */
    public function get($name, &$exists = TRUE) {
        $name = strtoupper(str_replace('-', '_', $name));
        if (isset($this[$name])) {
            return $this[$name];
        }
        $exists = FALSE;
        return '';
    }

    /**
     * Set header
     *
     * @param string $name      Header type
     * @param string $value     Header content
     */
    public function set($name, $value) {
        return header("$name: $value");
    }

    /**
     * Set HTTP Status
     *
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     *     
     * @param integer $status   HTTP status
     */
    public function status($status) {
        static $http_status = [
            100=>'100 Continue',
            101=>'101 Switching Protocols',
            102=>'102 Processing',
            103=>'103 Early Hints',
            201=>'201 Created',
            202=>'202 Accepted',
            203=>'203 Non-Authoritative Information',
            204=>'204 No Content',
            205=>'205 Reset Content',
            206=>'206 Partial Content',
            207=>'207 Multi-Status',
            208=>'208 Already Reported',
            226=>'226 IM Used',
            300=>'300 Multiple Choices',
            301=>'301 Moved Permanently',
            302=>'302 Found',
            303=>'303 See Other',
            304=>'304 Not Modified',
            305=>'305 Use Proxy',
            306=>'306 Switch Proxy',
            307=>'307 Temporary Redirect',
            308=>'308 Permanent Redirect',
            400=>'400 Bad Request',
            401=>'401 Unauthorized',
            402=>'402 Payment Required',
            403=>'403 Forbidden',
            404=>'404 Not Found',
            405=>'405 Method Not Allowed',
            406=>'406 Not Acceptable',
            407=>'407 Proxy Authentication Required',
            408=>'408 Request Timeout',
            409=>'409 Conflict',
            410=>'410 Gone',
            411=>'411 Length Required',
            412=>'412 Precondition Failed',
            413=>'413 Payload Too Large',
            414=>'414 URI Too Long',
            415=>'415 Unsupported Media Type',
            416=>'416 Range Not Satisfiable',
            417=>'417 Expectation Failed',
            418=>'418 I\'m a teapot',
            421=>'421 Misdirected Request',
            422=>'422 Unprocessable Entity',
            423=>'423 Locked',
            424=>'424 Failed Dependency',
            426=>'426 Upgrade Required',
            428=>'428 Precondition Required',
            429=>'429 Too Many Requests',
            431=>'431 Request Header Fields Too Large',
            451=>'451 Unavailable For Legal Reasons',
            500=>'500 Internal Server Error',
            501=>'501 Not Implemented',
            502=>'502 Bad Gateway',
            503=>'503 Service Unavailable',
            504=>'504 Gateway Timeout',
            505=>'505 HTTP Version Not Supported',
            506=>'506 Variant Also Negotiates',
            507=>'507 Insufficient Storage',
            508=>'508 Loop Detected',
            510=>'510 Not Extended',
            511=>'511 Network Authentication Required'
        ];
        $status = (int)$status;
        $value  = isset($http_status[$status]) ? $http_status[$status] : 'Unknown Status';
        header("HTTP/1.1 $value", TRUE, $status);
    } // status 
}
