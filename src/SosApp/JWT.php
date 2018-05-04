<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp;

use SosApp\OpenSSL as SosAppEncryptor;

class JWT
{
    const HS256 = 'v1';
    const HS384 = 'v2';
    const HS512 = 'v3';

    const JWT256 = 'j1';
    const JWT384 = 'j2';
    const JWT512 = 'j3';

    public static function decode($jwt, $key = null, $verify = true)
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            return FALSE;
        }
        list($header, $payloadb64, $cryptob64) = $tks;
        if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($payloadb64))) {
            return FALSE;
        }
        $sig = JWT::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if (empty($header)) {
                return FALSE;
            }
            if ($sig != JWT::sign("$header.$payloadb64", $key, $header)) {
                return FALSE;
            }
        }
        return $payload;
    }

    public static function encode($payload, $key, $algo = JWT::JWT256)
    {
        $segments = array();
        $segments[] = $algo;
        $segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($payload));
        $signing_input = implode('.', $segments);

        $signature = JWT::sign($signing_input, $key, $algo);
        $segments[] = JWT::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    public static function decrypt($jwt, $key = null, $verify = true)
    {
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            return FALSE;
        }
        list($header, $payloadb64, $cryptob64) = $tks;
        if (null === $payload = JWT::jsonDecode(SosAppEncryptor::decrypt(JWT::urlsafeB64Decode($payloadb64), $key, SosAppEncryptor::FORMAT_RAW))) {
            return FALSE;
        }
        $sig = JWT::urlsafeB64Decode($cryptob64);
        if ($verify) {
            if ($header == '' || $sig != JWT::sign("$header.$payloadb64", $key, $header)) {
                return FALSE;
            }
        }
        return $payload;
    }

    public static function encrypt($payload, $key, $algo = JWT::HS256)
    {
        $segments = array();
        $segments[] = $algo;
        $segments[] = JWT::urlsafeB64Encode(SosAppEncryptor::encrypt(JWT::jsonEncode($payload), $key, SosAppEncryptor::FORMAT_RAW));
        $signing_input = implode('.', $segments);

        $signature = JWT::sign($signing_input, $key, $algo);
        $segments[] = JWT::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    public static function sign($msg, $key, $method = JWT::HS256)
    {
        $methods = array(
            'v1' => 'sha256',
            'v2' => 'sha384',
            'v3' => 'sha512',
            'j1' => 'sha256',
            'j2' => 'sha384',
            'j3' => 'sha512'
        );        
        if (empty($methods[$method])) {
            return FALSE;
        }
        return hash_hmac($methods[$method], $msg, $key, true);
    }

    public static function jsonDecode($input)
    {
        $obj = json_decode($input, TRUE);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            return FALSE;
        } else if ($obj === null && $input !== 'null') {
            return FALSE;
        }
        return $obj;
    }

    public static function jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            JWT::handleJsonError($errno);
        }
        else if ($json === 'null' && $input !== null) {
            throw new \DomainException('Null result with non-null input');
        }
        return $json;
    }

    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function handleJsonError($errno)
    {
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON'
        );
        throw new \DomainException(isset($messages[$errno])
            ? $messages[$errno]
            : 'Unknown JSON error: ' . $errno
        );
    }
}
