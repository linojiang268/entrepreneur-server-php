<?php
namespace intg\Entrepreneur\Controllers\Wap;

use intg\Entrepreneur\TestCase;
use \PHPUnit_Framework_Assert as Assert;

class WeixinControllerTest extends TestCase
{

    //=========================================
    //      goToOauth
    //=========================================
    public function testGoToOauthUrlSuccessfully()
    {
        $url = '/wap/weixin/oauth/go?' . http_build_query([
            'redirect_url'  => 'http://domain/logic',
        ]);
        $response = $this->call('GET', $url);
        Assert::assertEquals(302, $response->status());     // check redirect status
        Assert::assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        Assert::assertEquals(true, 0 === strpos($response->getTargetUrl(), 'https://open.weixin.qq.com/connect/oauth2/authorize'));
    }

    public function testGoToOauthUrlFailed_MissRedirectUrl()
    {
        $response = $this->call('GET', '/wap/weixin/oauth/go');

        Assert::assertEquals(302, $response->status());     // check redirect status
        Assert::assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        Assert::assertEquals(true, false === strpos($response->getTargetUrl(), 'https://open.weixin.qq.com/connect/oauth2/authorize'));
    }

    public function testGoToOauthUrlFailed_WrongRedirectUrl()
    {
        $url = '/wap/weixin/oauth/go?' . http_build_query([
            'redirect_url'  => 'domain.com/logic',  // scheme missed
        ]);
        $response = $this->call('GET', $url);

        Assert::assertEquals(302, $response->status());     // check redirect status
        Assert::assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        Assert::assertEquals(true,  false === strpos($response->getTargetUrl(), 'https://open.weixin.qq.com/connect/oauth2/authorize'));
    }

    //=========================================
    //      doOauth
    //=========================================
    public function testDoOauthSuccessfully()
    {
        $this->mockWeixinService(1234);
        $response = $this->call('GET', 'wap/weixin/oauth?code=1234&state=' . urlencode('https://domain:8080/logic?openid=test'));

        Assert::assertEquals(302, $response->status());     // check redirect status
        Assert::assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        Assert::assertEquals('https://domain:8080/logic?openid=OPENID', $response->getTargetUrl());
    }

    private function mockWeixinService($code)
    {
        $weixinService = \Mockery::mock(\Entrepreneur\Services\WeixinService::class);
        $oauthInfo = (new \Entrepreneur\Models\WechatToken())
            ->setOpenid('OPENID')
            ->setWebTokenAccess('ACCESS_TOKEN')
            ->setWebTokenExpireAt(new \DateTime(date('Y-m-d H:i:s', time() + 7200)))
            ->setWebTokenRefresh('REFRESH_TOKEN');
        $weixinService->shouldReceive('getWebOauthInfo')->with($code)->andReturn($oauthInfo);

        $this->app[\Entrepreneur\Services\WeixinService::class] = $weixinService;

        return $weixinService;
    }
}
