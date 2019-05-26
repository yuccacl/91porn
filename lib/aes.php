<?php

class aes{
    private $key;

    /*
     构造函数
     @param key 密钥
     @param type 加密类型：1、mcrypt；2、openssl
     */
    public function __construct($key){
        $this->key = $key;
    }
    public function encrypt($data){
        $key = $this->key;
        // openssl_encrypt 加密不同Mcrypt，对秘钥长度要求，超出16加密结果不变
        $data = openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

        $data = strtolower(bin2hex($data));
        return base64_encode($data);
    }
    public function decrypt($data){
        $key = $this->key;
        $decrypted = openssl_decrypt(hex2bin(base64_decode($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        return $decrypted;
    }
    /**
     *仅用于QQ登录验证
     * @param string $string 需要加密的字符串
     * @param string $key 密钥
     * @return string
     */
    public static function encryptQQ($string, $key)
    {

        // openssl_encrypt 加密不同Mcrypt，对秘钥长度要求，超出16加密结果不变
        $data = openssl_encrypt($string, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

        $data = strtolower(bin2hex($data));

        return $data;
    }
    /**用于QQ登录验证
     * @param string $string 需要解密的字符串
     * @param string $key 密钥
     * @return string
     */
    public static function decryptQQ($string, $key)
    {
        $decrypted = openssl_decrypt(hex2bin($string), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

        return $decrypted;
    }
}