<?php
/**
 * Created by PhpStorm.
 * User: dhdj
 * Date: 1/15/18
 * Time: 9:09 AM
 * 本类因为在ITXE平台上使用,所以加上了Doc注释,请Rsplwe自行观摩
 */
class Security{
    /**
     * 构造函数,这里完全没有调用就不用管他了
     */
    public function __construct(){

    }

    /**
     * @param $method
     * @param $password
     * @return string
     * SetMP函数是在你开始加密解密前必须要调用一次的,因为他指明了加密的手段和密钥
     */
    public function SetMP($method, $password){
        $this->method = $method;
        $this->password = $password;
        return json_encode(array("Method"=>$this->method,"Password"=>$this->password));//返回json格式的数据,这个你根本不需要调用,别管就好了
    }

    /**
     * @param $plaintext
     * @return string
     * 加密,傻子都可以看懂
     */
    public function Encrypt($plaintext){
        $ivlen = openssl_cipher_iv_length($cipher=$this->method);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $this->password, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $this->password, $as_binary=true);
        $ciphertext = gzencode($iv.$hmac.$ciphertext_raw,9);
        return $ciphertext;//返回二进制格式
    }

    /**
     * @param $ciphertext
     * @return bool|string
     * 解密,傻子都可以看懂
     */
    public function Decrypt($ciphertext){//gzip压缩后的aes加密后的字符串
        $c = gzdecode($ciphertext);
        $ivlen = openssl_cipher_iv_length($cipher=$this->method);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $this->password, $options=OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $this->password, $as_binary=true);
        if (hash_equals($hmac, $calcmac)) {//PHP 5.6+ timing attack safe comparison安全保护
            return $original_plaintext;//解密正确返回原文
        }else{
            return false;//如果解密错误,返回false
        }
    }
}