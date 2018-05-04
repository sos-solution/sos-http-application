<?php
/**
 * SOS PHP Framework
 *
 * @link      https://github.com/sos-solution/sos-http-application for the canonical source repository
 * @copyright Copyright (c) 2012-2018, SOS Solution Limited (Hong Kong). (https://www.sos-solution.com/)
 * @license   https://github.com/sos-solution/sos-http-application/blob/master/LICENSE.md New BSD License
 */

namespace SosApp;

class OpenSSL
{
    private $cipher_algo;
    private $hash_algo;
    private $iv_num_bytes;
    private $format;

    const FORMAT_RAW = 0;
    const FORMAT_B64 = 1;
    const FORMAT_HEX = 2;

    public function __construct($cipher_algo = 'aes-256-ctr', $hash_algo = 'sha256', $fmt = self::FORMAT_B64) {
        $this->cipher_algo = $cipher_algo;
        $this->hash_algo = $hash_algo;
        $this->format = $fmt;

        if (!in_array($cipher_algo, openssl_get_cipher_methods(true))) {
            throw new \Exception("SosApp\\OpenSSL:: - unknown cipher algo {$cipher_algo}");
        }

        if (!in_array($hash_algo, openssl_get_md_methods(true))) {
            throw new \Exception("SosApp\\OpenSSL:: - unknown hash algo {$hash_algo}");
        }

        $this->iv_num_bytes = openssl_cipher_iv_length($cipher_algo);
    }

    static public function randomKey($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function encryptString($in, $key, $fmt = null) {
        if ($fmt === null) {
            $fmt = $this->format;
        }

        // Build an initialisation vector
        $iv = openssl_random_pseudo_bytes($this->iv_num_bytes, $isStrongCrypto);        
        if (!$isStrongCrypto) {
            throw new \Exception("SosApp\\OpenSSL::encryptString() - Not a strong key");
        }

        // Hash the key
        $keyhash = openssl_digest($key, $this->hash_algo, true);

        // and encrypt
        $opts =  OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($in, $this->cipher_algo, $keyhash, $opts, $iv);

        if ($encrypted === false) {
            throw new \Exception('SosApp\\OpenSSL::encryptString() - Encryption failed: ' . openssl_error_string());
        }

        // The result comprises the IV and encrypted data
        $res = $iv . $encrypted;

        // and format the result if required.
        if ($fmt == self::FORMAT_B64) {
            $res = base64_encode($res);
        }
        else if ($fmt == self::FORMAT_HEX) {
            $res = unpack('H*', $res)[1];
        }

        return $res;
    }

    public function decryptString($in, $key, $fmt = null)
    {
        if ($fmt === null) {
            $fmt = $this->format;
        }

        $raw = $in;

        // Restore the encrypted data if encoded
        if ($fmt == self::FORMAT_B64) {
            $raw = base64_decode($in);
        }
        elseif ($fmt == self::FORMAT_HEX) {
            $raw = pack('H*', $in);
        }

        // and do an integrity check on the size.
        if (strlen($raw) < $this->iv_num_bytes) {
            throw new \Exception('SosApp\\OpenSSL::decryptString() - ' .
                'data length ' . strlen($raw) . " is less than iv length {$this->iv_num_bytes}");
        }

        // Extract the initialisation vector and encrypted data
        $iv = substr($raw, 0, $this->iv_num_bytes);
        $raw = substr($raw, $this->iv_num_bytes);

        // Hash the key
        $keyhash = openssl_digest($key, $this->hash_algo, true);

        // and decrypt.
        $opts = OPENSSL_RAW_DATA;
        $res = openssl_decrypt($raw, $this->cipher_algo, $keyhash, $opts, $iv);

        if ($res === false) {
            throw new \Exception('SosApp\\OpenSSL::decryptString - decryption failed: ' . openssl_error_string());
        }

        return $res;
    }

    public static function encrypt($in, $key, $fmt = null) {
        $c = new self();        
        return $c->encryptString($in, $key, $fmt);
    }

    public static function decrypt($in, $key, $fmt = null) {
        $c = new self();
        return $c->decryptString($in, $key, $fmt);
    }

    public static function createKeyPair($bits = 1024, $alg = 'sha1') {
        $config = array(
            "digest_alg" => $alg,
            "private_key_bits" => $bits,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res);
        $publicKey = $publicKey["key"];
        return ['publicKey'=>$publicKey, 'privateKey'=>$privateKey];
    }

    public static function publicEncrypt($data, $publicKey, $fmt = self::FORMAT_B64) {
        openssl_public_encrypt($data, $encrypted, $publicKey);
         if ($fmt == self::FORMAT_B64) {
            $encrypted = base64_encode($encrypted);
        }
        else if ($fmt == self::FORMAT_HEX) {
            $encrypted = unpack('H*', $encrypted)[1];
        }
        return $encrypted;
    }

    public static function publicDecrypt($encrypted, $publicKey, $fmt = self::FORMAT_B64) {
        if ($fmt == self::FORMAT_B64) {
            $encrypted = base64_decode($encrypted);
        }
        elseif ($fmt == self::FORMAT_HEX) {
            $encrypted = pack('H*', $encrypted);
        }

        openssl_public_decrypt($encrypted, $decrypted, $publicKey);
        return $decrypted;
    }

    public static function privateEncrypt($data, $privateKey, $fmt = self::FORMAT_B64) {
        openssl_private_encrypt($data, $encrypted, $privateKey);

        if ($fmt == self::FORMAT_B64) {
            $encrypted = base64_encode($encrypted);
        }
        else if ($fmt == self::FORMAT_HEX) {
            $encrypted = unpack('H*', $encrypted)[1];
        }

        return $encrypted;
    }

    public static function privateDecrypt($encrypted, $privateKey, $fmt = self::FORMAT_B64) {
        if ($fmt == self::FORMAT_B64) {
            $encrypted = base64_decode($encrypted);
        }
        elseif ($fmt == self::FORMAT_HEX) {
            $encrypted = pack('H*', $encrypted);
        }

        openssl_private_decrypt($encrypted, $decrypted, $privateKey);
        return $decrypted;
    }

}