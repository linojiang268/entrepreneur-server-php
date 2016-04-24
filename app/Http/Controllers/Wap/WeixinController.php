<?php
namespace Entrepreneur\Http\Controllers\Wap;

use Entrepreneur\Services\WxMsgService;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Entrepreneur\Http\Controllers\Controller;
use Entrepreneur\Services\WeixinService;

class WeixinController extends Controller
{
    /**
     * index
     */
    public function index(Request $request, WeixinService $weixinService, Guard $auth)
    {
        $wechat = $request->session()->get('weixin');

        try {
            // grab JS access token and store it if needed
            $weixinService->getJsSignPackage($request->fullUrl());
        } catch (\Exception $ex) {
            // ignore
        }

        $user = null;
        if (!$auth->guest()) {
            $user = $auth->user();
        } else {
            if (($openId = array_get($wechat, 'openid', null)) != null) {
                // @TODO find user from openId
            }
        }

        $data = [
            'wechat_openid'  => array_get($wechat, 'openid', null),
            'wechat_success' => intval(array_get($wechat, 'success')),
            'wechat_session' => intval($request->session()->has('wechat')),
            'user'           => $user == null ? null : [
                'mobile' => $user->mobile,
                'name'  => $user->name,
                'business' => $user->business
            ]
        ];

        if (!$data['wechat_success'] || $data['wechat_success'] == null) {
            $request->session()->forget('wechat');
        }

        return view('wap.weixin.index', $data);
    }

    /**
     * Go to wechat oauth, after oauth finished, page will be redirect to
     * redirect url (go to oauth parameter) with user openid, for example:
     *      redirect url is: http://domain/logic, after oauth finished,
     *      page will be redirect to: http://domain/logic?openid=xxxxx
     */
    public function goToOauth(Request $request, WeixinService $weixinService)
    {
        $this->validate($request, [
            'redirect_url'          => 'required|url|max:128',
        ], [
            'redirect_url.required'     => '授权回跳页面未填写',
            'redirect_url.url'          => '授权回跳页面格式错误',
            'redirect_url.max'          => '授权回跳页面格式错误',
        ]);

        $doOauthUrl = url('wap/weixin/oauth');
        $url = $weixinService->getOauthUrl($doOauthUrl,
                                           $request->input('redirect_url'));

        return redirect($url);
    }

    /**
     * Invoked by Wechat with oauth code, page will be redirect to redirect url set by
     * oauthUrl controller after oauth finished
     */
    public function doOauth(Request $request, WeixinService $weixinService)
    {
        $this->validate($request, [
            'code'     => 'required|string|max:128',
            'state'    => 'required|string|max:128',        // redirect_url after oauth
        ], [
            'code.required'         => '授权code未填写',
            'code.string'           => '授权code格式错误',
            'code.max'              => '授权code格式错误',
            'state.required'        => 'state未填写',
            'state.string'          => 'state格式错误',
            'state.max'             => 'state格式错误',
        ]);

        try {
            $wechatToken = $weixinService->getWebOauthInfo($request->input('code'));
        } catch (\Exception $ex) {
            $wechatToken = null;
        }
        $openid = $wechatToken ? $wechatToken->getOpenid() : 'null';

        // store openid in session
        $request->session()->put('weixin', [
            'success'   => $openid ? true : false,
            'openid'    => $openid,
            'oauthTime' => time(),
        ]);

        $redirectUrl = $this->assembleOauthRedirectUrl(
            $request->input('state'), ['openid' => $openid]
        );
        return redirect($redirectUrl);
    }

    private function assembleOauthRedirectUrl($redirectUrl, array $params)
    {
        $urlElements = parse_url($redirectUrl);

        // put params into query string
        $queryArrays = [];
        if (isset($urlElements['query'])) {
            parse_str($urlElements['query'], $queryArrays);
            unset($queryArrays['openid']);
        }
        $queryString = http_build_query(array_merge($queryArrays, $params));

        $url = (isset($urlElements['scheme']) ? $urlElements['scheme'] : 'http') . '://' .
               (isset($urlElements['host']) ? $urlElements['host'] : '') . 
               (isset($urlElements['port']) ? ':' . $urlElements['port'] : '') .
               (isset($urlElements['path']) ? $urlElements['path'] : '/') .
               ($queryString ? ('?' . $queryString) : '') .
               (isset($urlElements['fragment']) ? ('#' . $urlElements['fragment']) : '');

        return $url;
    }

    public function msg(Request $request, WxMsgService $wxMsgService)
    {
        if ($request->isMethod('GET')) {
            if ($request->has('echostr')) { // wx verification - echo the 'echostr' given after signature check passes
                if ($wxMsgService->checkEchoSignature($request->get('signature'),
                    $request->get('timestamp'),
                    $request->get('nonce'))
                ) {
                    echo $request->get('echostr');
                } else {
                    echo "Sorry! check signature failed.";
                }
            }

            // don't know what kind of request it is
            return null;
        } else {
            // data is posted via request body, but you can find it from $_POST
            // the only way to extract it is from 'php://input' stream.
            // And what's more, the posted data is supposed to be XML.
            $requestXml = $wxMsgService->parseRequest(file_get_contents('php://input'));
            $req = simplexml_load_string($requestXml);

            $method = null;
            if ($req->MsgType == 'event') { // it's event
                $method = 'on' . self::saneEventKey($req->EventKey) . self::saneEvent($req->Event);
            }

            if ($method != null) { // todo: check the existence of action
                if (method_exists($wxMsgService, $method)) {
                    return $wxMsgService->encryptResponse($wxMsgService->$method($req));
                }
            }

            return 'Not supported yet';
        }
    }

    private static function saneEventKey($eventKey)
    {
        // 'latest_requirement' -> 'LatestRequirement'
        return str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($eventKey))));
    }

    private static function saneEvent($event)
    {
        // 'CLICK' --> 'Clicked'
        return str_replace(' ', '', ucfirst(strtolower($event))) . 'ed';
    }

}
