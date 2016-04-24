<?php

namespace Entrepreneur\Services\Wx;

/**
 * 1.第三方回复加密消息给公众平台；
 * 2.第三方收到公众平台发送的消息，验证消息的安全性，并对消息进行解密。
 */
class BizMsgCrypt
{
    private $token;
    private $encodingAesKey;
    private $appId;

    /**
     * 构造函数
     * @param $token string 公众平台上，开发者设置的token
     * @param $encodingAesKey string 公众平台上，开发者设置的EncodingAESKey
     * @param $appId string 公众平台的appId
     */
    public function __construct($token, $encodingAesKey, $appId)
    {
        $this->token = $token;
        $this->encodingAesKey = $encodingAesKey;
        $this->appId = $appId;
    }

    /**
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param $replyMsg string 公众平台待回复用户的消息，xml格式的字符串
     * @param $timeStamp string 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @param $nonce string 随机串，可以自己生成，也可以用URL参数的nonce
     *
     * @return string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串
     */
    public function encryptMsg($replyMsg, $timeStamp = null, $nonce = null)
    {
        $pc = new Prpcrypt($this->encodingAesKey);

        //加密
        $encrypt = $pc->encrypt($replyMsg, $this->appId);
        if ($timeStamp == null) {
            $timeStamp = time();
        }

        if ($nonce == null) {
            $nonce = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 16);
        }

        //生成安全签名
        $sha1 = new Sha1();
        $signature = $sha1->getSHA1($this->token, $timeStamp, $nonce, $encrypt);

        //生成发送的xml
        $xmlParser = new XMLParser();
        return $xmlParser->generate($encrypt, $signature, $timeStamp, $nonce);
    }


    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param $msgSignature string 签名串，对应URL参数的msg_signature
     * @param $timestamp string 时间戳 对应URL参数的timestamp
     * @param $nonce string 随机串，对应URL参数的nonce
     * @param $postData string 密文，对应POST请求的数据
     *
     * @return string $msg  解密后的原文
     */
    public function decryptMsg($postData)
    {
        if (strlen($this->encodingAesKey) != 43) {
            return null;
        }

        $pc = new Prpcrypt($this->encodingAesKey);

        //提取密文
        $xmlparse = new XMLParser();
        list($encrypt, $touser_name) = $xmlparse->extract($postData);

//        if ($timestamp == null) {
//            $timestamp = time();
//        }

//        //验证安全签名
//        $sha1 = new Sha1();
//        $signature = $sha1->getSHA1($this->token, $timestamp, $nonce, $encrypt);
//        if ($signature != $msgSignature) {
//            return null;
//        }

        return $pc->decrypt($encrypt, $this->appId);
    }

}

