<?php
namespace Entrepreneur\Services;

use Cache;
use Entrepreneur\Models\WechatToken;
use Storage;
use \GuzzleHttp\ClientInterface;
use \GuzzleHttp\RequestOptions;

class WeixinService
{
    const OAUTH_SCOPE_BASE = 'snsapi_base';
    const PREFIX_KEY = 'weixin_';
    const TICKET = 'ticket';
    const TOKEN = 'access_token';

    const URL_FOR_GET_COMPANY_TICKET = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=%s";
    const URL_FOR_GET_TICKET = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=%s";

    const URL_FOR_GET_COMPANY_TOKEN = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=%s&corpsecret=%s";
    const URL_FOR_GET_TOKEN = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s";

    const URL_FOR_OAUTH_GET_CODE = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const URL_FOR_OAUTH_GET_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const URL_FOR_REFRESH_WEB_ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    const URL_FOR_FETCH_USERINFO_FROM_WEB = 'https://api.weixin.qq.com/sns/userinfo';

    private $appId;
    private $appSecret;
    private $company;

    /**
     * Http client
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @param ClientInterface $httpClient
     * @param array           $option      keys taken:
     *                                      - app_id     (string)required
     *                                      - app_secret (string)required
     *                                      - company    (bool)required
     */
    public function __construct(ClientInterface $httpClient,
                                array $option
    ) {
        $this->httpClient = $httpClient;
        $this->appId      = array_get($option, 'app_id');
        $this->appSecret  = array_get($option, 'app_secret');
        $this->company    = array_get($option, 'company');
    }

    /**
     * get sign package of wx js
     *
     * @param $url    注意 URL 一定要动态获取，不能 hardcode.
     * @return array  keys taken:
     *                 - appId
     *                 - nonceStr
     *                 - timestamp
     *                 - url
     *                 - signature
     *                 - rawString
     */
    public function getJsSignPackage($url)
    {
        $jsapiTicket = $this->getJsTicket();

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );

        return $signPackage;
    }

    //=================================
    //          Oauth
    //=================================
    /**
     * Get Oauth url, user will redirect to this url, then wechat server will
     * redirect to the url specifed in Oauth url with oauth code
     *
     * @param string $doOauthUrl        wechat will redirect to this url with oauth code
     * @param string $redirectUrl       redirect url after oauth finished
     *
     * @return string                   oauth url
     */
    public function getOauthUrl($doOauthUrl, $redirectUrl)
    {
        $scope = self::OAUTH_SCOPE_BASE;

        return self::URL_FOR_OAUTH_GET_CODE . '?' . http_build_query([
            'appid'         => $this->appId,
            'redirect_uri'  => $doOauthUrl,
            'response_type' => 'code',
            'scope'         => $scope,
            'state'         => $redirectUrl,
        ]) . '#wechat_redirect';
    }

    /**
     * Get Web oauth info by code
     *
     * @param string $code      code from wx
     *
     * @return \Entrepreneur\Models\WechatToken|null
     */
    public function getWebOauthInfo($code)
    {
        $url = self::URL_FOR_OAUTH_GET_TOKEN . '?' . http_build_query([
            'appid'         => $this->appId,
            'secret'        => $this->appSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
        ]);

        $res = json_decode($this->httpGet($url));
        if ( ! $res ||isset($res->errcode)) {
            return null;
        }

        return (new WechatToken)->setOpenid($res->openid);
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsTicket()
    {
        $key = self::TICKET;

        $ticket = $this->getParamFromLocal($key);

        if (!$this->checkParamExpireTime($ticket)) {
            $token = $this->getJsToken();

            if ($this->company) {
                $ticket = $this->getParamFromWechat(sprintf(self::URL_FOR_GET_COMPANY_TICKET,
                                                            $token),
                                                    $key);
            } else {
                $ticket = $this->getParamFromWechat(sprintf(self::URL_FOR_GET_TICKET,
                                                            $token),
                                                    $key);
            }

            if (!$this->checkParamExpireTime($ticket)) {
                throw new \Exception('get ticket fail');
            }

            $this->saveParamToLocal($key, $ticket);
        }

        return $ticket->$key;
    }

    private function getJsToken()
    {
        $key = self::TOKEN;

        $token = $this->getParamFromLocal($key);

        if (!$this->checkParamExpireTime($token)) {
            if ($this->company) {
                $token = $this->getParamFromWechat(sprintf(self::URL_FOR_GET_COMPANY_TOKEN,
                                                           $this->appId,
                                                           $this->appSecret),
                                                   $key);
            } else {
                $token = $this->getParamFromWechat(sprintf(self::URL_FOR_GET_TOKEN,
                                                           $this->appId,
                                                           $this->appSecret),
                                                   $key);
            }

            if (!$this->checkParamExpireTime($token)) {
                throw new \Exception('get access_token fail');
            }

            $this->saveParamToLocal($key, $token);
        }

        return $token->$key;
    }

    private function checkParamExpireTime($value)
    {
        if (is_null($value) || $value->app_id != $this->appId || $value->expire_time < time()) {
            return false;
        }

        return true;
    }

    /**
     * get param from local (from cache or from storage file)
     *
     * @param $name  name of param
     * @return       std value of param
     */
    private function getParamFromLocal($name)
    {
        $key = self::PREFIX_KEY . $name;
        if (Cache::has($key)) {
            if (!empty($value = Cache::get($key))) {
                return json_decode($value);
            }
        }

        if (Storage::exists($key)) {
            if (!empty($value = Storage::get($key))) {
                $value = json_decode($value);

                $this->saveParamToLocal($name, $value);
                return $value;
            }
        }

        return null;
    }

    /**
     * get param from wechat by network
     *
     * @param string $requestUrl  url for get the param value
     * @param string $name        name of param
     * @return \stdClass          std value of param
     */
    private function getParamFromWechat($requestUrl, $name)
    {
        $res = json_decode($this->httpGet($requestUrl));

        $ret = new \stdClass();
        $ret->app_id = $this->appId;

        if (property_exists($res, $name)) {
            $ret->$name       = $res->$name;
            $ret->expire_time = time() + $res->expires_in - 200;
        } else {
            $ret->$name       = '';
            $ret->expire_time = 0;
        }

        return $ret;
    }

    private function httpGet($url) {
        $options = [
            RequestOptions::TIMEOUT => 500,
            RequestOptions::VERIFY  => false,
        ];

        $response = $this->httpClient->request('GET', $url, $options);
        if ($response->getStatusCode() != 200) {
            throw new \Exception('bad response from wxjs: ' . (string)$response->getBody());
        }

        return (string) $response->getBody();
    }

    /**
     * save param to local (in cache and storage file)
     *
     * @param string $name   name of param
     * @param mixed $value  std value of param
     */
    private function saveParamToLocal($name, $value)
    {
        $key = self::PREFIX_KEY . $name;
        Cache::forever($key, json_encode($value));

        Storage::put($key, json_encode($value));
    }

}
